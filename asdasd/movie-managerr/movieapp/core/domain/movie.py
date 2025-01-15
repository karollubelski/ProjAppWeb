from typing import Optional, List
from datetime import date
from pydantic import UUID4, BaseModel, HttpUrl, ConfigDict

class MovieIn(BaseModel):
    """Model representing movie's DTO attributes."""
    title: str
    language: str
    description: Optional[str] = None
    release_date: Optional[date] = None
    genres: List[str] = []
    runtime: int
    streamings: List[str]  = []

class MovieBroker(MovieIn):
    """A broker class including user in the model."""
    user_id: UUID4
    
class Movie(MovieBroker):
    """Model representing movies attributes in the database."""
    id: int
    model_config = ConfigDict(from_attributes=True, extra="ignore")