from motor.motor_asyncio import AsyncIOMotorClient, AsyncIOMotorDatabase
from typing import Optional
# MONGO_URL = "mongodb://127.0.0.1:27017"

# client = AsyncIOMotorClient(MONGO_URL)
# db = client.artify


# Configuration MongoDB
MONGO_URL = "mongodb://127.0.0.1:27017"
DATABASE_NAME = "artify"
 
# Variables globales
client: Optional[AsyncIOMotorClient] = None
db: Optional[AsyncIOMotorDatabase] = None
 
 
async def connect_to_mongo():
    """Établit la connexion à MongoDB"""
    global client, db
    client = AsyncIOMotorClient(MONGO_URL)
    db = client[DATABASE_NAME]
    print("✓ Connecté à MongoDB")
 
 
async def close_mongo_connection():
    """Ferme la connexion à MongoDB"""
    global client
    if client is not None:
        client.close()
        print("✓ Déconnecté de MongoDB")
 
 
def get_database() -> AsyncIOMotorDatabase:
    """Retourne l'instance de la base de données"""
    if db is None:
        raise RuntimeError("Database not initialized. Call connect_to_mongo() first.")
    return db