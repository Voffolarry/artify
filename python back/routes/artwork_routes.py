from fastapi import APIRouter, HTTPException, status
from typing import List
from bson import ObjectId
from database import get_database
from models.artwork import Artwork, ArtworkCreate, ArtworkUpdate
 
router = APIRouter(prefix="/api/artworks", tags=["artworks"])
 
 
# CREATE - Créer une nouvelle œuvre
@router.post(
    "/",
    response_model=Artwork,
    status_code=status.HTTP_201_CREATED,
    summary="Créer une nouvelle œuvre d'art"
)
async def create_artwork(artwork: ArtworkCreate):
    """
    Crée une nouvelle œuvre d'art dans la base de données.
    """
    db = get_database()
    
    # Vérifier que l'artiste existe
    if not ObjectId.is_valid(artwork.artist_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'artiste invalide"
        )
    
    artist = await db.artists.find_one({"_id": ObjectId(artwork.artist_id)})
    if not artist:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Artiste non trouvé"
        )
    
    artwork_data = artwork.model_dump()
    result = await db.artworks.insert_one(artwork_data)
    created_artwork = await db.artworks.find_one({"_id": result.inserted_id})
    return created_artwork
 
 
# READ - Obtenir toutes les œuvres
@router.get(
    "/",
    response_model=List[Artwork],
    summary="Lister toutes les œuvres d'art"
)
async def get_all_artworks(skip: int = 0, limit: int = 10):
    """
    Retourne la liste de toutes les œuvres avec pagination.
    """
    db = get_database()
    artworks = await db.artworks.find().skip(skip).limit(limit).to_list(limit)
    return artworks
 
 
# READ - Obtenir une œuvre par ID
@router.get(
    "/{artwork_id}",
    response_model=Artwork,
    summary="Obtenir une œuvre d'art par ID"
)
async def get_artwork(artwork_id: str):
    """
    Retourne les détails d'une œuvre spécifique.
    """
    db = get_database()
    
    if not ObjectId.is_valid(artwork_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'œuvre invalide"
        )
    
    artwork = await db.artworks.find_one({"_id": ObjectId(artwork_id)})
    
    if not artwork:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Œuvre non trouvée"
        )
    
    return artwork
 
 
# UPDATE - Mettre à jour une œuvre
@router.put(
    "/{artwork_id}",
    response_model=Artwork,
    summary="Mettre à jour une œuvre d'art"
)
async def update_artwork(artwork_id: str, artwork_update: ArtworkUpdate):
    """
    Met à jour les informations d'une œuvre.
    """
    db = get_database()
    
    if not ObjectId.is_valid(artwork_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'œuvre invalide"
        )
    
    update_data = {k: v for k, v in artwork_update.model_dump().items() if v is not None}
    
    if not update_data:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Aucune donnée à mettre à jour"
        )
    
    result = await db.artworks.update_one(
        {"_id": ObjectId(artwork_id)},
        {"$set": update_data}
    )
    
    if result.matched_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Œuvre non trouvée"
        )
    
    updated_artwork = await db.artworks.find_one({"_id": ObjectId(artwork_id)})
    return updated_artwork
 
 
# DELETE - Supprimer une œuvre
@router.delete(
    "/{artwork_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    summary="Supprimer une œuvre d'art"
)
async def delete_artwork(artwork_id: str):
    """
    Supprime une œuvre de la base de données.
    """
    db = get_database()
    
    if not ObjectId.is_valid(artwork_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'œuvre invalide"
        )
    
    result = await db.artworks.delete_one({"_id": ObjectId(artwork_id)})
    
    if result.deleted_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Œuvre non trouvée"
        )
    
    return None
 
 
# BONUS - Obtenir les œuvres d'un artiste
@router.get(
    "/artist/{artist_id}",
    response_model=List[Artwork],
    summary="Obtenir les œuvres d'un artiste"
)
async def get_artworks_by_artist(artist_id: str, skip: int = 0, limit: int = 10):
    """
    Retourne toutes les œuvres d'un artiste spécifique.
    """
    db = get_database()
    
    if not ObjectId.is_valid(artist_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'artiste invalide"
        )
    
    artworks = await db.artworks.find(
        {"artist_id": artist_id}
    ).skip(skip).limit(limit).to_list(limit)
    
    return artworks
 
 
# BONUS - Rechercher des œuvres par catégorie
@router.get(
    "/category/{category}",
    response_model=List[Artwork],
    summary="Obtenir les œuvres par catégorie"
)
async def get_artworks_by_category(category: str, skip: int = 0, limit: int = 10):
    """
    Retourne toutes les œuvres d'une catégorie spécifique.
    """
    db = get_database()
    
    artworks = await db.artworks.find(
        {"category": {"$regex": category, "$options": "i"}}
    ).skip(skip).limit(limit).to_list(limit)
    
    return artworks
 
 
# BONUS - Incrémenter les vues
@router.post(
    "/{artwork_id}/increment-views",
    response_model=Artwork,
    summary="Incrémenter les vues d'une œuvre"
)
async def increment_artwork_views(artwork_id: str):
    """
    Incrémente le nombre de vues d'une œuvre.
    """
    db = get_database()
    
    if not ObjectId.is_valid(artwork_id):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="ID d'œuvre invalide"
        )
    
    result = await db.artworks.update_one(
        {"_id": ObjectId(artwork_id)},
        {"$inc": {"views": 1}}
    )
    
    if result.matched_count == 0:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Œuvre non trouvée"
        )
    
    updated_artwork = await db.artworks.find_one({"_id": ObjectId(artwork_id)})
    return updated_artwork