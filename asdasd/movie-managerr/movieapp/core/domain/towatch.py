from pydantic import BaseModel, ConfigDict, UUID4


class ToWatchIn(BaseModel):
    movie_title: str

class ToWatchBroker(ToWatchIn):
    user_id: UUID4
    
class ToWatch(ToWatchIn):
    id: int
    user_id = UUID4
    model_config = ConfigDict(from_attributes=True, extra="ignore")