from abc import ABC, abstractmethod
from typing import Iterable

from movieapi.core.domain.location import StreamingPlatform, StreamingPlatformIn


class IStreamingService(ABC):
    @abstractmethod
    async def get_streaming_by_id(self, streaming_id: int) -> StreamingPlatform | None:
        pass

    @abstractmethod
    async def get_all_streamings(self) -> Iterable[StreamingPlatform]:
        pass

    @abstractmethod
    async def add_streaming(self, data: StreamingPlatformIn) -> StreamingPlatform | None:
        pass

    @abstractmethod
    async def update_streaming(
            self,
            streaming_id: int,
            data: StreamingPlatformIn,
    ) -> StreamingPlatform | None:
        pass

    @abstractmethod
    async def delete_streaming(self, streaming_id: int) -> bool:
        pass