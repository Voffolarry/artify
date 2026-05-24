from pydantic import BaseModel
from typing import List, Optional
from datetime import datetime


class Users(BaseModel):
    id: str
    name: str
    email: str
    password: str
    role: str = "client"
    favorite: List[str]
    registration_date: datetime = datetime.now()
