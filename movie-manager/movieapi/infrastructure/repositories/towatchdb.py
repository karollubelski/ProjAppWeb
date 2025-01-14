from typing import Any, Iterable

from asyncpg import Record  # type: ignore

from movieapi.core.domain.towatch import ToWatch, ToWatchIn
from movieapi.core.repositories.itowatch import IToWatchRepository
from movieapi.db import towatch_table, database


class ToWatchRepository(IToWatchRepository):

    async def get_towatch_by_id(self, towatch_id: int) -> Any | None:
        towatch = await self._get_by_id(towatch_id)
        return ToWatch(**dict(towatch)) if towatch else None

    async def get_all_towatches(self) -> Iterable[Any]:
        query = towatch_table.select().order_by(towatch_table.c.id.asc())
        towatches = await database.fetch_all(query)
        return [ToWatch(**dict(towatch)) for towatch in towatches]


    async def get_towatch_by_movie(self, movie_title: str) -> Iterable[Any]:
        query = towatch_table \
            .select() \
            .where(towatch_table.c.movie_title == movie_title) \
            .order_by(towatch_table.c.movie_title.asc())

        towatches = await database.fetch_all(query)
        return [ToWatch(**dict(to_watch)) for to_watch in towatches]



    async def get_towatch_by_user(self, user_name: str) -> Iterable[Any]:
        query = towatch_table \
            .select() \
            .where(towatch_table.c.user_name == user_name) \
            .order_by(towatch_table.c.user_name.asc())

        towatches = await database.fetch_all(query)
        return [ToWatch(**dict(to_watch)) for to_watch in towatches]

    async def add_towatch(self, data: ToWatchIn) -> Any | None:
        query = towatch_table.insert().values(**data.model_dump())
        new_towatch_id = await database.execute(query)
        new_towatch = await self._get_by_id(new_towatch_id)

        return ToWatch(**dict(new_towatch)) if new_towatch else None


    async def update_towatch(self, towatch_id: int, data: ToWatchIn) -> Any | None:

        if self._get_by_id(towatch_id):
            query = (
                towatch_table.update()
                .where(towatch_table.c.id == towatch_id)
                .values(**data.model_dump())
            )
            await database.execute(query)

            towatch = await self._get_by_id(towatch_id)

            return ToWatch(**dict(towatch)) if towatch else None

        return None


    async def delete_towatch(self, towatch_id: int) -> bool:

        if self._get_by_id(towatch_id):
            query = towatch_table \
                .delete() \
                .where(towatch_table.c.id == towatch_id)
            await database.execute(query)

            return True

        return False



    async def _get_by_id(self, towatch_id: int) -> Record | None:


        query = (
            towatch_table.select()
            .where(towatch_table.c.id == towatch_id)
        )

        return await database.fetch_one(query)