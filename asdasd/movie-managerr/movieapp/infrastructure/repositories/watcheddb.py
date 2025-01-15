from typing import Any, Iterable

from asyncpg import Record  # type: ignore

from movieapp.core.domain.watched import Watched, WatchedIn # Zmienione importy
from movieapp.core.repositories.iwatched import IWatchedRepository  # Zmieniony import

from movieapp.db import watched_table, database # Zmienione importy



class WatchedRepository(IWatchedRepository):

    async def get_watched_by_id(self, watched_id: int) -> Any | None: # Zmienione nazwy


        watched = await self._get_by_id(watched_id)


        return Watched(**dict(watched)) if watched else None # Zmieniona nazwa klasy


    async def get_watched_by_movie_title(self, movie_title: str) -> Iterable[Any]:  # Zmieniona nazwa

        query = watched_table.select().where(
            watched_table.c.movie_title == movie_title
        ).order_by(watched_table.c.movie_title.asc())

        watched_movies = await database.fetch_all(query)  # Zmieniona nazwa

        return [Watched(**dict(watched_movie)) for watched_movie in watched_movies]  # Zmieniona nazwa


    async def get_watched_by_user(self, user_id) -> Iterable[Any]:  #user_id # Zmieniona nazwa
        query = watched_table.select().where(watched_table.c.user_id == user_id).order_by(
            watched_table.c.movie_title.asc()
        )
        watched_movies = await database.fetch_all(query) #zmieniona nazwa

        return [Watched(**dict(watched_movie)) for watched_movie in watched_movies] # Zmieniona nazwa



    async def add_watched(self, data: WatchedIn) -> Any | None: # Zmieniona nazwa

        query = watched_table.insert().values(**data.model_dump()) #zmienione nazwy

        new_watched_id = await database.execute(query)  # Zmieniona nazwa
        new_watched = await self._get_by_id(new_watched_id) # Zmieniona nazwa


        return Watched(**dict(new_watched)) if new_watched else None # Zmieniona nazwa klasy


    async def update_watched(  # Zmieniona nazwa

        self,
        watched_id: int,  # Zmieniona nazwa
        data: WatchedIn,
    ) -> Any | None:

        if await self.get_watched_by_id(watched_id): #dodane
            query = (
                watched_table.update()  # Zmieniona nazwa tabeli
                .where(watched_table.c.id == watched_id)
                .values(**data.model_dump())
            )
            await database.execute(query)



            watched = await self._get_by_id(watched_id)


            return Watched(**dict(watched)) if watched else None


        return None



    async def delete_watched(self, watched_id: int) -> bool: # Zmieniona nazwa



        if await self._get_by_id(watched_id): #get_watched_by_id

            query = watched_table.delete().where(watched_table.c.id == watched_id)
            await database.execute(query)

            return True #dodane

        return False  #dodane


    async def _get_by_id(self, watched_id: int) -> Record | None:  # Zmieniona nazwa

        query = (
            watched_table.select()
            .where(watched_table.c.id == watched_id)

        )
        return await database.fetch_one(query)