from pydantic import BaseModel, ConfigDict


class StreamingPlatformIn(BaseModel):
    """Model representing streaming platform's DTO attributes."""
    name: str


class StreamingPlatform(StreamingPlatformIn):
    """Model representing streaming platforms's attributes in the database."""
    id: int

    model_config = ConfigDict(from_attributes=True, extra="ignore")