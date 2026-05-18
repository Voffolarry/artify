from pydantic import BaseModel
from datetime import datetime

class Reviews(BaseModel):
    id: str
    user_id: str
    artwork_id: str
    note: int
    comment: str 
    review_date: datetime = datetime.now()