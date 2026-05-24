from pydantic import BaseModel
from datetime import datetime

class Commands(BaseModel):
    id: str
    user_id: str
    artwork_id: str
    price: float
    payment_status: str = "Pending"
    command_date: datetime = datetime.now()