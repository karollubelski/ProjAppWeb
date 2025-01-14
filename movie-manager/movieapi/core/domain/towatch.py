from pydantic import BaseModel, ConfigDict


class ToWatchIn(BaseModel):
    user_name: str
    movie_title: str


class ToWatch(ToWatchIn):
    id: int
    model_config = ConfigDict(from_attributes=True, extra="ignore")