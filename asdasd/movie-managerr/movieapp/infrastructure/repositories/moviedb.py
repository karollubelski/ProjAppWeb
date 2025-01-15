from typing import Any, Iterable

from asyncpg import Record  # type: ignore
from sqlalchemy import select, join

from movieapp.core.repositories.imovie import IMovieRepository # Zmieniony import
from movieapp.core.domain.movie import Movie, MovieIn # Zmienione importy
from movieapp.db import (
    movie_table,
    user_table,
    database,
)
from movieapp.infrastructure.dto.moviedto import MovieDTO  # Nowy import



class MovieRepository(IMovieRepository):

    async def get_all_movies(self) -> Iterable[Any]:


        query = (
            select(movie_table, user_table)
            .select_from(
                join(
                        movie_table,
                        user_table,
                        movie_table.c.user_id == user_table.c.id
                    ),

            )
            .order_by(movie_table.c.title.asc())
        )

        movies = await database.fetch_all(query)

        return [MovieDTO.from_record(movie) for movie in movies]


    async def get_by_id(self, movie_id: int) -> Any | None:

        query = (
            select(movie_table, user_table)
            .select_from(
                join(
                    movie_table,
                    user_table,
                    movie_table.c.user_id == user_table.c.id
                )
            )

            .where(movie_table.c.id == movie_id)

            .order_by(movie_table.c.title.asc())
        )

        movie = await database.fetch_one(query)


        return MovieDTO.from_record(movie) if movie else None


    async def get_by_title(self, title: str) -> Any | None:

        query = (

            select(movie_table, user_table)
            .select_from(
                join(
                    movie_table,
                    user_table,
                    movie_table.c.user_id == user_table.c.id
                )
            )
            .where(movie_table.c.title == title)
            .order_by(movie_table.c.title.asc())

        )

        movie = await database.fetch_one(query)

        return MovieDTO.from_record(movie) if movie else None



    async def get_by_language(self, language: str) -> Iterable[Any]: # Dodane


        query = movie_table \
            .select() \
            .where(movie_table.c.language == language) \
            .order_by(movie_table.c.title.asc())

        movies = await database.fetch_all(query)

        return [Movie(**dict(movie)) for movie in movies]

    async def get_by_genre(self, genre: str) -> Iterable[Any]:

        # Filtrujemy po tablicy gatunków.
        query = movie_table.select().where(movie_table.c.genres.contains([genre])).order_by(movie_table.c.title.asc())

        movies = await database.fetch_all(query)


        return [Movie(**dict(movie)) for movie in movies]




    async def get_by_runtime(self, runtime: int) -> Iterable[Any]:

        query = movie_table \
            .select() \
            .where(movie_table.c.runtime == runtime) \
            .order_by(movie_table.c.title.asc())
        movies = await database.fetch_all(query)

        return [Movie(**dict(movie)) for movie in movies]



    async def get_by_user(self, user_id) -> Iterable[Any]:
        query = movie_table \
            .select() \
            .where(movie_table.c.user_id == user_id) \
            .order_by(movie_table.c.title.asc())

        movies = await database.fetch_all(query)

        return [Movie(**dict(movie)) for movie in movies]





    async def get_by_streaming(self, streaming: str) -> Iterable[Any]:

        query = movie_table.select().where(movie_table.c.streamings.contains([streaming])).order_by(movie_table.c.title.asc())  # contains() for array

        movies = await database.fetch_all(query)

        return [Movie(**dict(movie)) for movie in movies]

    async def filter_by_runtime(self, runtime_start: int, runtime_stop: int) -> Iterable[Any]:
        query = movie_table \
            .select() \
            .where(movie_table.c.runtime >= runtime_start) \
            .where(movie_table.c.runtime <= runtime_stop) \
            .order_by(movie_table.c.title.asc())
        movies = await database.fetch_all(query)

        return [Movie(**dict(movie)) for movie in movies]

    async def add_movie(self, data: MovieIn) -> Any | None:

        query = movie_table.insert().values(**data.model_dump())

        new_movie_id = await database.execute(query)
        new_movie = await self._get_by_id(new_movie_id)


        return Movie(**dict(new_movie)) if new_movie else None


    async def update_movie(

        self,
        movie_id: int,
        data: MovieIn,

    ) -> Any | None:


        if await self.get_by_id(movie_id):
            query = (
                movie_table.update()
                .where(movie_table.c.id == movie_id)
                .values(**data.model_dump())
            )
            await database.execute(query)

            movie = await self._get_by_id(movie_id)


            return Movie(**dict(movie)) if movie else None


        return None



    async def delete_movie(self, movie_id: int) -> bool:

        if await self.get_by_id(movie_id):
            query = movie_table \
                .delete() \
                .where(movie_table.c.id == movie_id)
            await database.execute(query)


            return True

        return False




    async def _get_by_id(self, movie_id: int) -> Record | None:

        query = (
            movie_table.select()
            .where(movie_table.c.id == movie_id)
            .order_by(movie_table.c.title.asc())

        )



        return await database.fetch_one(query)