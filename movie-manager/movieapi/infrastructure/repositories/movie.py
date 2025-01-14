from typing import Any, Iterable, List
from datetime import date

from asyncpg import Record  # type: ignore
from sqlalchemy import select, join, func, text

from movieapi.core.repositories.imovie import IMovieRepository
from movieapi.core.domain.movie import Movie, MovieIn
from movieapi.db import (
    movie_table,
    streaming_table,
    user_table,
    movie_streamings,
    database,
)
from movieapi.infrastructure.dto.moviedto import MovieDTO


class MovieRepository(IMovieRepository):

    async def get_all_movies(self) -> Iterable[Any]:
        query = (
            select(movie_table, streaming_table, user_table)
            .select_from(
                join(
                    join(
                        movie_table,
                        streaming_table,
                        movie_table.c.streaming_id == streaming_table.c.id
                    ),
                    user_table,
                    movie_table.c.user_id == user_table.c.id
                )
            )
            .order_by(movie_table.c.title.asc())
        )
        movies = await database.fetch_all(query)
        return [MovieDTO.from_record(movie) for movie in movies]

    async def get_by_streaming(self, streaming_id: int) -> Iterable[Any]:
        query = movie_table \
            .select() \
            .where(movie_table.c.streaming_id == streaming_id) \
            .order_by(movie_table.c.title.asc())
        movies = await database.fetch_all(query)
        return [Movie(**dict(movie)) for movie in movies]


    async def get_by_user(self, user_name: str) -> Iterable[Any]:
        query = movie_table \
            .select() \
            .where(movie_table.c.user_name == user_name) \
            .order_by(movie_table.c.title.asc())
        movies = await database.fetch_all(query)
        return [Movie(**dict(movie)) for movie in movies]


    async def get_by_id(self, movie_id: int) -> Any | None:

        streaming_subquery = select(
           [
                func.array_agg(streaming_table.c.id).label("streaming_ids"),
                func.array_agg(streaming_table.c.name).label("streaming_names"),
           ]
        ).select_from(
             movie_streamings.join(streaming_table, streaming_table.c.id == movie_streamings.c.streaming_id)
        ).where(
            movie_streamings.c.movie_id == movie_id

        ).alias("streaming_data")


        query = select(
           [movie_table, user_table, streaming_subquery]
        ).select_from(
           movie_table.join(user_table, movie_table.c.user_id == user_table.c.id).outerjoin(streaming_subquery, True)
           .filter(movie_table.c.id == movie_id)
        )

        movie = await database.fetch_one(query)


        return MovieDTO.from_record(movie) if movie else None


    async def get_by_title(self, title: str) -> Any | None:
        query = (
            select(movie_table, streaming_table, user_table)
            .select_from(
                join(
                    join(
                        movie_table,
                        streaming_table,
                        movie_table.c.streaming_id == streaming_table.c.id
                    ),
                    user_table,
                    movie_table.c.user_id == user_table.c.id
                )
            )
            .where(movie_table.c.title == title)
            .order_by(movie_table.c.title.asc())
        )
        movie = await database.fetch_one(query)

        return MovieDTO.from_record(movie) if movie else None


    async def get_by_language(self, language: str) -> Iterable[Any]:
        query = movie_table \
            .select() \
            .where(movie_table.c.language == language) \
            .order_by(movie_table.c.title.asc())
        movies = await database.fetch_all(query)
        return [Movie(**dict(movie)) for movie in movies]


    async def get_by_runtime(self, runtime: int) -> Iterable[Any]:
        query = movie_table \
            .select() \
            .where(movie_table.c.runtime == runtime) \
            .order_by(movie_table.c.title.asc())
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

        try:
            poster_url_str = str(data.poster_url) if data.poster_url else None


            query_movie = movie_table.insert().values(
                title=data.title,
                language=data.language,
                description=data.description,
                release_date=data.release_date,
                genres=data.genres,
                poster_url=poster_url_str,
                runtime=data.runtime,
                user_id=data.user_id,
                user_name=data.user_name
            ).returning(movie_table.c.id)


            new_movie_id = await database.execute(query_movie)


            for streaming_id in data.streaming_ids:
                query_movie_streamings = movie_streamings.insert().values(
                    movie_id=new_movie_id,
                    streaming_id=streaming_id
                )
                await database.execute(query_movie_streamings)

            new_movie = await self._get_by_id(new_movie_id) 

            return Movie(**dict(new_movie)) if new_movie else None


        except Exception as e:
            
            return None



    async def update_movie(
        self,
        movie_id: int,
        data: MovieIn,
    ) -> Any | None:
        if await self._get_by_id(movie_id):
            poster_url_str = str(data.poster_url) if data.poster_url else None

            try:
                query_update_movie = (
                    movie_table.update()
                    .where(movie_table.c.id == movie_id)
                    .values(
                        title=data.title,
                        language=data.language,
                        description=data.description,
                        release_date=data.release_date,
                        genres=data.genres,
                        poster_url=poster_url_str,
                        runtime=data.runtime,
                        user_id=data.user_id,
                        user_name=data.user_name,
                    )
                )
                await database.execute(query_update_movie)

                query_delete_streamings = (
                    movie_streamings.delete()
                    .where(movie_streamings.c.movie_id == movie_id)
                )
                await database.execute(query_delete_streamings)

                for streaming_id in data.streaming_ids:
                    query_insert_streamings = movie_streamings.insert().values(
                        movie_id=movie_id,
                        streaming_id=streaming_id
                    )
                    await database.execute(query_insert_streamings)


                updated_movie = await self._get_by_id(movie_id)
                return updated_movie

            except Exception as e:
                return None
            
        return None


    async def delete_movie(self, movie_id: int) -> bool:
        if await self._get_by_id(movie_id):
            query = movie_table.delete().where(movie_table.c.id == movie_id)
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