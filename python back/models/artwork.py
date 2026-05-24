from pydantic import BaseModel
from typing import List, Optional

class Artwork(BaseModel):
    id: str
    artist_id: str
    title: str
    medium: str
    price: float
    status: str = "Disponible"
    image_url: str
    year: Optional[int] = None
    category: List[str]
