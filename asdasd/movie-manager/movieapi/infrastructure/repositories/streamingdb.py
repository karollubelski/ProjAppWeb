from typing import Any, Iterable

from asyncpg import Record  # type: ignore

from movieapi.core.domain.location import StreamingPlatform, StreamingPlatformIn
from movieapi.core.repositories.istreaming import IStreamingRepository
from movieapi.db import streaming_table, database


class StreamingRepository(IStreamingRepository):

    async def get_streaming_by_id(self, streaming_id: int) -> Any | None:
        streaming = await self._get_by_id(streaming_id)
        return StreamingPlatform(**dict(streaming)) if streaming else None

    async def get_all_streamings(self) -> Iterable[Any]:
        query = streaming_table.select().order_by(streaming_table.c.name.asc())
        streamings = await database.fetch_all(query)
        return [StreamingPlatform(**dict(streaming)) for streaming in streamings]


    async def add_streaming(self, data: StreamingPlatformIn) -> Any | None:
        query = streaming_table.insert().values(**data.model_dump())
        new_streaming_id = await database.execute(query)
        new_streaming = await self._get_by_id(new_streaming_id)
        return StreamingPlatform(**dict(new_streaming)) if new_streaming else None


    async def update_streaming(
        self,
        streaming_id: int,
        data: StreamingPlatformIn,
    ) -> Any | None:
        if await self._get_by_id(streaming_id):
            query = (
                streaming_table.update()
                .where(streaming_table.c.id == streaming_id)
                .values(**data.model_dump())
            )
            await database.execute(query)

            streaming = await self._get_by_id(streaming_id)
            return StreamingPlatform(**dict(streaming)) if streaming else None
        return None


    async def delete_streaming(self, streaming_id: int) -> bool:
        if await self._get_by_id(streaming_id):
            query = streaming_table.delete().where(streaming_table.c.id == streaming_id)
            await database.execute(query)
            return True
        return False


    async def _get_by_id(self, streaming_id: int) -> Record | None:
        query = (
            streaming_table.select()
            .where(streaming_table.c.id == streaming_id)
            .order_by(streaming_table.c.name.asc())
        )
        return await database.fetch_one(query)