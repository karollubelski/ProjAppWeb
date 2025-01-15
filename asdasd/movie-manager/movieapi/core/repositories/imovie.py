from abc import ABC, abstractmethod
from typing import Any, Iterable

from movieapi.core.domain.movie import MovieIn


class IMovieRepository(ABC):
    @abstractmethod
    async def get_all_movies(self) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_by_streaming(self, streaming_id: int) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_by_user(self, user_name: str) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_by_id(self, movie_id: int) -> Any | None:
        pass

    @abstractmethod
    async def get_by_title(self, title: str) -> Any | None:
        pass

    @abstractmethod
    async def get_by_language(self, language: str) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_by_runtime(self, runtime: int) -> Iterable[Any]:
        pass

    @abstractmethod
    async def filter_by_runtime(self, runtime_start: int, runtime_stop: int) -> Iterable[Any]:
        pass


    @abstractmethod
    async def add_movie(self, data: MovieIn) -> Any | None:
        pass

    @abstractmethod
    async def update_movie(
        self,
        movie_id: int,
        data: MovieIn,
    ) -> Any | None:
        pass

    @abstractmethod
    async def delete_movie(self, movie_id: int) -> bool:
        pass