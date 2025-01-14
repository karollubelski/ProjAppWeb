from typing import Iterable

from movieapi.core.domain.location import StreamingPlatform, StreamingPlatformIn
from movieapi.core.repositories.istreaming import IStreamingRepository
from movieapi.infrastructure.services.istreaming import IStreamingService


class StreamingService(IStreamingService):
    _repository: IStreamingRepository

    def __init__(self, repository: IStreamingRepository) -> None:
        self._repository = repository

    async def get_streaming_by_id(self, streaming_id: int) -> StreamingPlatform | None:
        return await self._repository.get_streaming_by_id(streaming_id)

    async def get_all_streamings(self) -> Iterable[StreamingPlatform]:
        return await self._repository.get_all_streamings()

    async def add_streaming(self, data: StreamingPlatformIn) -> StreamingPlatform | None:
        return await self._repository.add_streaming(data)

    async def update_streaming(
        self,
        streaming_id: int,
        data: StreamingPlatformIn,
    ) -> StreamingPlatform | None:
        return await self._repository.update_streaming(
            streaming_id=streaming_id,
            data=data,
        )

    async def delete_streaming(self, streaming_id: int) -> bool:
        return await self._repository.delete_streaming(streaming_id)