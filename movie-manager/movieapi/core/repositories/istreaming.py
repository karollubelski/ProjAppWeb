from abc import ABC, abstractmethod
from typing import Any, Iterable

from movieapi.core.domain.location import StreamingPlatformIn


class IStreamingRepository(ABC):

    @abstractmethod
    async def get_streaming_by_id(self, streaming_id: int) -> Any | None:
        pass

    @abstractmethod
    async def get_all_streamings(self) -> Iterable[Any]:
        pass

    @abstractmethod
    async def add_streaming(self, data: StreamingPlatformIn) -> Any | None:
        pass

    @abstractmethod
    async def update_streaming(
        self,
        streaming_id: int,
        data: StreamingPlatformIn,
    ) -> Any | None:
        pass

    @abstractmethod
    async def delete_streaming(self, streaming_id: int) -> bool:
        pass