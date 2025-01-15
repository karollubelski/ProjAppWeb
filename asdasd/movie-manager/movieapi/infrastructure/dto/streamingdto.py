from pydantic import BaseModel, ConfigDict  # type: ignore


class StreamingDTO(BaseModel):
    id: int
    name: str

    model_config = ConfigDict(
        from_attributes=True,
        extra="ignore",
        arbitrary_types_allowed=True,
    )