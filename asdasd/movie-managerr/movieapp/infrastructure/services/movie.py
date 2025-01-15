from typing import Iterable, Any


from movieapp.core.domain.movie import Movie, MovieIn
from movieapp.core.repositories.imovie import IMovieRepository  # Zmieniony import
from movieapp.infrastructure.dto.moviedto import MovieDTO  # Dodany import
from movieapp.infrastructure.services.imovie import IMovieService  # Zmieniony import



class MovieService(IMovieService):  # Zmieniona nazwa

    _repository: IMovieRepository


    def __init__(self, repository: IMovieRepository) -> None:


        self._repository = repository



    async def get_all(self) -> Iterable[MovieDTO]:  # Zmieniona adnotacja typu

        return await self._repository.get_all_movies()


    async def get_by_id(self, movie_id: int) -> MovieDTO | None:

        return await self._repository.get_by_id(movie_id)




    async def get_by_title(self, title: str) -> MovieDTO | None:
        return await self._repository.get_by_title(title)

    async def get_by_language(self, language: str) -> Iterable[Movie]:  # Zmieniona adnotacja typu

        return await self._repository.get_by_language(language)


    async def get_by_genre(self, genre: str) -> Iterable[Movie]:  # Zmienione

        return await self._repository.get_by_genre(genre)




    async def get_by_runtime(self, runtime: int) -> Iterable[Movie]:

        return await self._repository.get_by_runtime(runtime)


    async def get_by_user(self, user_id) -> Iterable[Movie]:
        return await self._repository.get_by_user(user_id)


    async def get_by_streaming(self, streaming: str) -> Iterable[Movie]:
        return await self._repository.get_by_streaming(streaming)





    async def filter_by_runtime(self, runtime_start: int, runtime_stop: int) -> Iterable[Movie]:

        return await self._repository.filter_by_runtime(runtime_start, runtime_stop)

    async def add_movie(self, data: MovieIn) -> Movie | None:

        return await self._repository.add_movie(data)

    async def update_movie(
        self,
        movie_id: int,
        data: MovieIn,
    ) -> Movie | None: # Zmieniona adnotacja typu
        return await self._repository.update_movie(
            movie_id=movie_id,

            data=data,
        )


    async def delete_movie(self, movie_id: int) -> bool:
        return await self._repository.delete_movie(movie_id)