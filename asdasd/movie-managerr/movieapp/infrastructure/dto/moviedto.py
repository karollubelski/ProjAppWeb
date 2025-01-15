from typing import Optional, List
from datetime import date
from asyncpg import Record
from pydantic import UUID4, BaseModel, ConfigDict

from movieapp.infrastructure.dto.userdto import UserDTO  # Import


class MovieDTO(BaseModel):
    id: int
    title: str
    language: str
    description: Optional[str] = None
    release_date: Optional[date] = None
    genres: List[str] = []
    runtime: int
    streamings: List[str] = []

    user: UserDTO
    user_id: UUID4

    model_config = ConfigDict(
        from_attributes=True,
        extra="ignore",
        arbitrary_types_allowed=True,
    )

    @classmethod
    def from_record(cls, record: Record) -> "MovieDTO":
        record_dict = dict(record)
        return cls(
            id=record_dict.get("id"),
            title=record_dict.get("title"),
            language=record_dict.get("language"),
            description=record_dict.get("description"),
            release_date=record_dict.get("release_date"),
            genres=record_dict.get("genres"),  # Pobieranie listy gatunków
            runtime=record_dict.get("runtime"),
            streamings=record_dict.get("streamings"),
            user = UserDTO(

                id = record_dict.get("id_1"),
                name=record_dict.get("name"),
                email = record_dict.get("email"),

            ),
            user_id=record_dict.get("user_id"),


        )