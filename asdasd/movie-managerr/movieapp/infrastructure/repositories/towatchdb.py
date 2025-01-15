from typing import Any, Iterable

from asyncpg import Record  # type: ignore

from movieapp.core.domain.towatch import ToWatch, ToWatchIn # Zmienione importy
from movieapp.core.repositories.itowatch import IToWatchRepository # Zmieniony import
from movieapp.db import towatch_table, database



class ToWatchRepository(IToWatchRepository):

    async def get_towatch_by_id(self, towatch_id: int) -> Any | None:
        towatch = await self._get_by_id(towatch_id)
        return ToWatch(**dict(towatch)) if towatch else None

    async def get_all_towatch_movies(self) -> Iterable[Any]:  # Zmieniona nazwa

        query = towatch_table.select().order_by(towatch_table.c.movie_title.asc())

        towatch_movies = await database.fetch_all(query) # Zmieniona nazwa zmiennej

        return [ToWatch(**dict(towatch_movie)) for towatch_movie in towatch_movies]  # Zmieniona nazwa zmiennej


    async def get_towatch_by_movie_title(self, movie_title: str) -> Iterable[Any]:


        query = towatch_table \
            .select() \
            .where(towatch_table.c.movie_title == movie_title) \
            .order_by(towatch_table.c.movie_title.asc()) #movie title
        towatch_movies = await database.fetch_all(query)

        return [ToWatch(**dict(towatch_movie)) for towatch_movie in towatch_movies]

    async def get_towatch_by_user(self, user_id) -> Iterable[Any]: #user_id
        query = towatch_table \
            .select() \
            .where(towatch_table.c.user_id == user_id) \
            .order_by(towatch_table.c.movie_title.asc())  #movie title

        towatch_movies = await database.fetch_all(query) # Zmieniona nazwa

        return [ToWatch(**dict(towatch_movie)) for towatch_movie in towatch_movies] # Zmieniona nazwa


    async def add_towatch(self, data: ToWatchIn) -> Any | None:

        query = towatch_table.insert().values(**data.model_dump())
        new_towatch_id = await database.execute(query)  # Zmieniona nazwa zmiennej

        new_towatch = await self._get_by_id(new_towatch_id) # Zmieniona nazwa zmiennej
        return ToWatch(**dict(new_towatch)) if new_towatch else None


    async def update_towatch(

        self,
        towatch_id: int,

        data: ToWatchIn,

    ) -> Any | None:

        if await self._get_by_id(towatch_id):
            query = (
                towatch_table.update() #nazwa tabeli
                .where(towatch_table.c.id == towatch_id)
                .values(**data.model_dump())
            )
            await database.execute(query)

            towatch = await self._get_by_id(towatch_id) # Zmieniona nazwa zmiennej


            return ToWatch(**dict(towatch)) if towatch else None  # Zmieniona nazwa klasy


        return None


    async def delete_towatch(self, towatch_id: int) -> bool:  # Zmieniona nazwa

        if self.get_towatch_by_id(towatch_id):  # Zmieniona nazwa metody
            query = towatch_table.delete().where(towatch_table.c.id == towatch_id) #zmieniona nazwa tabeli
            await database.execute(query)
            return True #dodane

        return False #dodane

    async def _get_by_id(self, towatch_id: int) -> Record | None: #zmieniona nazwa zmiennej

        query = (
            towatch_table.select()
            .where(towatch_table.c.id == towatch_id)

        )
        return await database.fetch_one(query)