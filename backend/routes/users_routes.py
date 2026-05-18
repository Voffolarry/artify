from fastapi import APIRouter, HTTPException, status
from typing import List
from bson import ObjectId
from database import get_database
from models.users import Users, UserCreate, UserUpdate
 
router = APIRouter(prefix="/api/users", tags=["users"])
 
 
# CREATE - Créer un nouvel utilisateur
@router.post(
    "/",
    response_model=Users,
    status_code=status.HTTP_201_CREATED,
    summary="Créer un nouvel utilisateur"
)
async def create_user(user: UserCreate):
    """
    Crée un nouvel utilisateur.
    """
    db = get_database()
    
    # Vérifier si l'email existe déjà
    existing_user = await db.users.find_one({"email": user.email})
    if existing_user:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Cet email est déjà utilisé"
        )
    
    # Préparer les données
    user_data = user.model_dump()
    
    # Insérer dans MongoDB
    result = await db.users.insert_one(user_data)
    
    # Récupérer l'utilisateur créé
    created_user = await db.users.find_one({"_id": result.inserted_id})
    return created_user
 
 
# READ - Obtenir tous les utilisateurs
@router.get(
    "/",
    response_model=List[Users],
    summary="Lister tous les utilisateurs"
)
async def get_all_users(skip: int = 0, limit: int = 10):
    """
    Retourne la liste de tous les utilisateurs avec pagination.
    """
    db = get_database()
    users = await db.users.find().skip(skip).limit(limit).to_list(limit)
    return users
 
 
# READ - Obtenir un utilisateur par ID
@router.get(
    "/{user_id}",
    response_model=Users,
    summary="Obtenir un utilisateur par ID"
)
async def get_user(user_id: str):
    """
    Retourne les détails d'un utilisateur spécifique.
    """
    db = get_database()
    
    # Valider l'ObjectId
    if not ObjectId.is_valid(user_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'utilisateur invalide"
        )
    
    user = await db.users.find_one({"_id": ObjectId(user_id)})
    
    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Utilisateur non trouvé"
        )
    
    return user
 
 
# UPDATE - Mettre à jour un utilisateur
@router.put(
    "/{user_id}",
    response_model=Users,
    summary="Mettre à jour un utilisateur"
)
async def update_user(user_id: str, user_update: UserUpdate):
    """
    Met à jour les informations d'un utilisateur.
    """
    db = get_database()
    
    # Valider l'ObjectId
    if not ObjectId.is_valid(user_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'utilisateur invalide"
        )
    
    # Préparer les données à mettre à jour (ignorer les champs None)
    update_data = {k: v for k, v in user_update.model_dump().items() if v is not None}
    
    if not update_data:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Aucune donnée à mettre à jour"
        )
    
    # Mettre à jour
    result = await db.users.update_one(
        {"_id": ObjectId(user_id)},
        {"$set": update_data}
    )
    
    if result.matched_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Utilisateur non trouvé"
        )
    
    # Retourner l'utilisateur mis à jour
    updated_user = await db.users.find_one({"_id": ObjectId(user_id)})
    return updated_user
 
 
# DELETE - Supprimer un utilisateur
@router.delete(
    "/{user_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Supprimer un utilisateur"
)
async def delete_user(user_id: str):
    """
    Supprime un utilisateur de la base de données.
    """
    db = get_database()
    
    # Valider l'ObjectId
    if not ObjectId.is_valid(user_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'utilisateur invalide"
        )
    
    result = await db.users.delete_one({"_id": ObjectId(user_id)})
    
    if result.deleted_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Utilisateur non trouvé"
        )
    
    return None
 
 
# BONUS - Ajouter un artwork aux favoris
@router.post(
    "/{user_id}/favorites/{artwork_id}",
    response_model=Users,
    summary="Ajouter un artwork aux favoris"
)
async def add_to_favorites(user_id: str, artwork_id: str):
    """
    Ajoute un artwork à la liste des favoris d'un utilisateur.
    """
    db = get_database()
    
    # Valider les ObjectIds
    if not ObjectId.is_valid(user_id) or not ObjectId.is_valid(artwork_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID invalide"
        )
    
    result = await db.users.update_one(
        {"_id": ObjectId(user_id)},
        {"$addToSet": {"favorite": artwork_id}}  # $addToSet évite les doublons
    )
    
    if result.matched_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Utilisateur non trouvé"
        )
    
    updated_user = await db.users.find_one({"_id": ObjectId(user_id)})
    return updated_user
 
 
# BONUS - Supprimer un artwork des favoris
@router.delete(
    "/{user_id}/favorites/{artwork_id}",
    response_model=Users,
    summary="Supprimer un artwork des favoris"
)
async def remove_from_favorites(user_id: str, artwork_id: str):
    """
    Supprime un artwork de la liste des favoris d'un utilisateur.
    """
    db = get_database()
    
    # Valider les ObjectIds
    if not ObjectId.is_valid(user_id) or not ObjectId.is_valid(artwork_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID invalide"
        )
    
    result = await db.users.update_one(
        {"_id": ObjectId(user_id)},
        {"$pull": {"favorite": artwork_id}}
    )
    
    if result.matched_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Utilisateur non trouvé"
        )
    
    updated_user = await db.users.find_one({"_id": ObjectId(user_id)})
    return updated_user