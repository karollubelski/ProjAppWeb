from pydantic import BaseModel, ConfigDict


class WatchedIn(BaseModel):
    user_name: str
    movie_title: str


class Watched(WatchedIn):
    id: int
    model_config = ConfigDict(from_attributes=True, extra="ignore")