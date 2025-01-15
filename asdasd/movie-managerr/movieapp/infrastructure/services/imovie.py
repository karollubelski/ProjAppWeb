from abc import ABC, abstractmethod
from typing import Iterable, Any

from movieapp.core.domain.movie import Movie, MovieIn # Zmieniony import
from movieapp.infrastructure.dto.moviedto import MovieDTO  # Dodany import



class IMovieService(ABC):

    @abstractmethod
    async def get_all(self) -> Iterable[MovieDTO]:
        pass


    @abstractmethod
    async def get_by_id(self, movie_id: int) -> MovieDTO | None: # Zmieniona nazwa i typ
        pass


    @abstractmethod
    async def get_by_title(self, title: str) -> MovieDTO | None:
        pass


    @abstractmethod
    async def get_by_language(self, language: str) -> Iterable[Movie]:  # Dodana adnotacja typu
        pass

    @abstractmethod
    async def get_by_genre(self, genre: str) -> Iterable[Movie]:  # Dodane
        pass

    @abstractmethod
    async def get_by_runtime(self, runtime: int) -> Iterable[Movie]:
        pass



    @abstractmethod
    async def get_by_streaming(self, streaming: str) -> Iterable[Movie]:
        pass

    @abstractmethod
    async def get_by_user(self, user_id) -> Iterable[Movie]: #user_id
        pass



    @abstractmethod
    async def filter_by_runtime(self, runtime_start: int, runtime_stop: int) -> Iterable[Movie]:
        pass


    @abstractmethod
    async def add_movie(self, data: MovieIn) -> Movie | None:
        pass


    @abstractmethod
    async def update_movie(
        self,
        movie_id: int,
        data: MovieIn,
    ) -> Movie | None: # Zmieniony typ
        pass


    @abstractmethod
    async def delete_movie(self, movie_id: int) -> bool:
        pass