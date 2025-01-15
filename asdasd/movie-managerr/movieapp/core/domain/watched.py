from pydantic import BaseModel, ConfigDict, UUID4

class WatchedIn(BaseModel):
    user_name: str
    movie_title: str

class WatchedBroker(WatchedIn):
    user_id: UUID4

class Watched(WatchedIn):
    id: int
    user_id: UUID4
    model_config = ConfigDict(from_attributes=True, extra="ignore")