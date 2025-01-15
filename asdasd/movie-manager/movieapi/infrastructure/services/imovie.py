from abc import ABC, abstractmethod
from typing import Iterable, Any, List, Dict, Tuple
from datetime import date


from movieapi.core.domain.movie import Movie, MovieIn
from movieapi.infrastructure.dto.moviedto import MovieDTO


class IMovieService(ABC):

    @abstractmethod
    async def get_all(self) -> Iterable[MovieDTO]:
        pass

    @abstractmethod
    async def get_summary_by_streaming(self, streaming_id: int) -> Iterable[Any]:
        pass

    @abstractmethod
    async def get_summary_by_all_streamings(self) -> Iterable[Any]:
        pass


    @abstractmethod
    async def get_by_streaming(self, streaming_id: int) -> Iterable[Movie]:
        pass

    @abstractmethod
    async def get_by_id(self, movie_id: int) -> MovieDTO | None:
        pass

    @abstractmethod
    async def get_by_title(self, title: str) -> MovieDTO | None:
        pass


    @abstractmethod
    async def get_by_language(self, language: str) -> Iterable[Movie]:
        pass


    @abstractmethod
    async def get_by_runtime(self, runtime: int) -> Iterable[Movie]:
        pass

    @abstractmethod
    async def filter_by_runtime(self, runtime_start: int, runtime_stop: int) -> Iterable[Movie]:
        pass

    @abstractmethod
    async def get_by_user(self, user_name: str) -> Iterable[Movie]:
        pass


    @abstractmethod
    async def add_movie(self, data: MovieIn) -> Movie | None:
        pass


    @abstractmethod
    async def update_movie(
        self,
        movie_id: int,
        data: MovieIn,
    ) -> Movie | None:
        pass


    @abstractmethod
    async def delete_movie(self, movie_id: int) -> bool:
        pass