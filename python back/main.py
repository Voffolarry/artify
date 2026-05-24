from fastapi import FastAPI
from contextlib import asynccontextmanager

from fastapi.middleware.cors import CORSMiddleware


# Imports des routes
from routes import artists_routes, users_routes, artwork_routes
 
# Import de la base de données
from database import connect_to_mongo, close_mongo_connection
 
 
# Gestion du cycle de vie de l'application
@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup
    print("Application starting...")
    await connect_to_mongo()
    yield
    # Shutdown
    print("Application shut down...")
    await close_mongo_connection()
 
 
# Créer l'instance FastAPI avec gestion du cycle de vie
app = FastAPI(
    title="Artify API",
    description="API pour la plateforme Artify de partage d'œuvres d'art",
    version="1.0.0",
    lifespan=lifespan
)
 
# Configuration CORS (à adapter selon vos besoins)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:3000", "http://localhost:8000"],  # Adapter avec votre frontend
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
 
# Routes
app.include_router(artists_routes.router)
app.include_router(users_routes.router)
app.include_router(artwork_routes.router)
# app.include_router(reviews_routes.router)
# app.include_router(commands_routes.router) 
 
# Route de base
@app.get("/")
async def home():
    """Endpoint de bienvenue"""
    return {
        "message": "Bienvenue sur l'API Artify!",
        "version": "1.0.0",
        "docs": "/docs",
        "redoc": "/redoc"
    }
 
 
# Health check
@app.get("/health")
async def health_check():
    """Vérifier la santé de l'API"""
    return {
        "status": "ok",
        "message": "L'API fonctionne correctement"
    }
 
 
if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=8000,
        reload=True
    )