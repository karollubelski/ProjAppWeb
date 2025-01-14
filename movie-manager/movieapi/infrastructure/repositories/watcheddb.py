from typing import Any, Iterable

from asyncpg import Record  # type: ignore

from movieapi.core.domain.watched import Watched, WatchedIn
from movieapi.core.repositories.iwatched import IWatchedRepository
from movieapi.db import watched_table, database


class WatchedRepository(IWatchedRepository):
    async def get_watched_by_id(self, watched_id: int) -> Any | None:
        watched = await self._get_by_id(watched_id)
        return Watched(**dict(watched)) if watched else None

    async def get_all_watcheds(self) -> Iterable[Any]:
        query = watched_table.select().order_by(watched_table.c.id.asc())
        watcheds = await database.fetch_all(query)
        return [Watched(**dict(watched)) for watched in watcheds]

    async def get_watched_by_movie(self, movie_title: str) -> Iterable[Any]:
        query = watched_table \
            .select() \
            .where(watched_table.c.movie_title == movie_title) \
            .order_by(watched_table.c.movie_title.asc())
        watcheds = await database.fetch_all(query)
        return [Watched(**dict(watched)) for watched in watcheds]

    async def get_watched_by_user(self, user_name: str) -> Iterable[Any]:
        query = watched_table \
            .select() \
            .where(watched_table.c.user_name == user_name) \
            .order_by(watched_table.c.user_name.asc())
        watcheds = await database.fetch_all(query)
        return [Watched(**dict(watched)) for watched in watcheds]

    async def add_watched(self, data: WatchedIn) -> Any | None:
        query = watched_table.insert().values(**data.model_dump())
        new_watched_id = await database.execute(query)
        new_watched = await self._get_by_id(new_watched_id)
        return Watched(**dict(new_watched)) if new_watched else None


    async def update_watched(self, watched_id: int, data: WatchedIn) -> Any | None:
        if await self._get_by_id(watched_id):
            query = (
                watched_table.update()
                .where(watched_table.c.id == watched_id)
                .values(**data.model_dump())
            )
            await database.execute(query)

            watched = await self._get_by_id(watched_id)

            return Watched(**dict(watched)) if watched else None

        return None


    async def delete_watched(self, watched_id: int) -> bool:
        if self._get_by_id(watched_id):
            query = watched_table.delete().where(watched_table.c.id == watched_id)
            await database.execute(query)
            return True
        return False


    async def _get_by_id(self, watched_id: int) -> Record | None:
        query = (
            watched_table.select()
            .where(watched_table.c.id == watched_id)
        )
        return await database.fetch_one(query)