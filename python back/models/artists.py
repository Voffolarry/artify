from pydantic import BaseModel
from typing import Optional

class Artists(BaseModel):
    id: str
    name: str
    surname: str
    country: str
    speciality: str
    photo_url: Optional[str] = None

class ArtistCreate:
    name: str
    surname: str
    country: str
    speciality: str
class ArtistUpdate:
    
