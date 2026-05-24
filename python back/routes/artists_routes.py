from fastapi import APIRouter, HTTPException, status
from typing import List
from bson import ObjectId
from database import get_database
from models.artists import Artists, ArtistCreate, ArtistUpdate
 
router = APIRouter(prefix="/api/artists", tags=["artists"])
 
 
# CREATE - Créer un nouvel artiste
@router.post(
    "/",
    response_model=Artists,
    status_code=status.HTTP_201_CREATED,
    summary="Créer un nouvel artiste"
)
async def create_artist(artist: ArtistCreate):
    """
    Crée un nouvel artiste dans la base de données.
    """
    db = get_database()
    
    # Convertir le modèle Pydantic en dictionnaire
    artist_data = artist.model_dump()
    
    # Insérer dans MongoDB
    result = await db.artists.insert_one(artist_data)
    
    # Récupérer l'artiste créé
    created_artist = await db.artists.find_one({"_id": result.inserted_id})
    return created_artist
 
 
# READ - Obtenir tous les artistes
@router.get(
    "/",
    response_model=List[Artists],
    summary="Lister tous les artistes"
)
async def get_all_artists(skip: int = 0, limit: int = 10):
    """
    Retourne la liste de tous les artistes avec pagination.
    """
    db = get_database()
    artists = await db.artists.find().skip(skip).limit(limit).to_list(limit)
    return artists
 
 
# READ - Obtenir un artiste par ID
@router.get(
    "/{artist_id}",
    response_model=Artists,
    summary="Obtenir un artiste par ID"
)
async def get_artist(artist_id: str):
    """
    Retourne les détails d'un artiste spécifique.
    """
    db = get_database()
    
    # Valider l'ObjectId
    if not ObjectId.is_valid(artist_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'artiste invalide"
        )
    
    artist = await db.artists.find_one({"_id": ObjectId(artist_id)})
    
    if not artist:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Artiste non trouvé"
        )
    
    return artist
 
 
# UPDATE - Mettre à jour un artiste
@router.put(
    "/{artist_id}",
    response_model=Artists,
    summary="Mettre à jour un artiste"
)
async def update_artist(artist_id: str, artist_update: ArtistUpdate):
    """
    Met à jour les informations d'un artiste.
    """
    db = get_database()
    
    # Valider l'ObjectId
    if not ObjectId.is_valid(artist_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'artiste invalide"
        )
    
    # Préparer les données à mettre à jour (ignorer les champs None)
    update_data = {k: v for k, v in artist_update.model_dump().items() if v is not None}
    
    if not update_data:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Aucune donnée à mettre à jour"
        )
    
    # Mettre à jour
    result = await db.artists.update_one(
        {"_id": ObjectId(artist_id)},
        {"$set": update_data}
    )
    
    if result.matched_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Artiste non trouvé"
        )
    
    # Retourner l'artiste mis à jour
    updated_artist = await db.artists.find_one({"_id": ObjectId(artist_id)})
    return updated_artist
 
 
# DELETE - Supprimer un artiste
@router.delete(
    "/{artist_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Supprimer un artiste"
)
async def delete_artist(artist_id: str):
    """
    Supprime un artiste de la base de données.
    """
    db = get_database()
    
    # Valider l'ObjectId
    if not ObjectId.is_valid(artist_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'artiste invalide"
        )
    
    result = await db.artists.delete_one({"_id": ObjectId(artist_id)})
    
    if result.deleted_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Artiste non trouvé"
        )
    
    return None
 
 
# BONUS - Rechercher des artistes par spécialité
@router.get(
    "/speciality/{speciality}",
    response_model=List[Artists],
    summary="Rechercher des artistes par spécialité"
)
async def get_artists_by_speciality(speciality: str, skip: int = 0, limit: int = 10):
    """
    Retourne tous les artistes ayant une spécialité spécifique.
    """
    db = get_database()
    artists = await db.artists.find(
        {"speciality": {"$regex": speciality, "$options": "i"}}
    ).skip(skip).limit(limit).to_list(limit)
    
    return artists