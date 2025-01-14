from typing import Optional, List
from datetime import date
from pydantic import UUID4, BaseModel, HttpUrl, ConfigDict
from asyncpg import Record  # type: ignore

from movieapi.infrastructure.dto.streamingdto import StreamingDTO


class MovieDTO(BaseModel):
    id: int
    title: str
    language: str
    description: Optional[str] = None
    release_date: Optional[date] = None
    genres: Optional[List[str]] = None
    poster_url: Optional[str] = None 
    runtime: int
    streamings: List[StreamingDTO] = []
    user_id: UUID4
    user_name: str

    model_config = ConfigDict(
        from_attributes=True,
        extra="ignore",
        arbitrary_types_allowed=True,
    )

    @classmethod
    def from_record(cls, record: Record) -> "MovieDTO":

        record_dict = dict(record)
        streaming_ids = record_dict.get("streaming_ids")
        streaming_names = record_dict.get("streaming_names")

        streamings = []

        if streaming_ids is not None and streaming_names is not None:
            if isinstance(streaming_ids, list) and isinstance(streaming_names, list):
                streamings = [StreamingDTO(id=sid, name=sname) for sid, sname in zip(streaming_ids, streaming_names)]

        poster_url = record_dict.get("poster_url")

        if isinstance(poster_url, str) :
           poster_url = poster_url
        elif poster_url:
           poster_url = str(poster_url) 


        return cls(
            id=record_dict.get("id"),
            title=record_dict.get("title"),
            language=record_dict.get("language"),
            description=record_dict.get("description"),
            release_date=record_dict.get("release_date"),
            genres=record_dict.get("genres"),
            poster_url = poster_url,
            runtime=record_dict.get("runtime"),
            streamings=streamings,
            user_id=record_dict.get("user_id"),
            user_name=record_dict.get("user_name"),
        )