from typing import Optional, Iterable, Any, List, Dict, Tuple

from datetime import date

from movieapi.core.domain.movie import Movie, MovieIn
from movieapi.core.repositories.imovie import IMovieRepository
from movieapi.infrastructure.dto.moviedto import MovieDTO
from movieapi.infrastructure.services.imovie import IMovieService



class MovieService(IMovieService):
    _repository: IMovieRepository

    def __init__(self, repository: IMovieRepository) -> None:
        self._repository = repository

    async def get_all(self) -> Iterable[MovieDTO]:
        return await self._repository.get_all_movies()

    async def get_summary_by_streaming(self, streaming_id: int) -> Optional[Dict[str, Any]]: 
        movies_on_streaming = await self._repository.get_by_streaming(streaming_id)
        summary = {}
        runtime_sum = 0
        movie_count = 0

        for movie in movies_on_streaming:
            runtime_sum += movie.runtime
            movie_count += 1

        if movie_count > 0:
            summary['average_runtime'] = runtime_sum / movie_count
            summary['total_movies'] = movie_count
        else:
            summary['average_runtime'] = 0
            summary['total_movies'] = 0

        return summary



    async def get_summary_by_all_streamings(self) -> Dict[int, Any]:

        all_streamings_ids = set()
        all_movies = await self.get_all()
        summary: Dict[int, Dict[str, Any]] = {}


        for movie in all_movies:
            all_streamings_ids.add(movie.streaming.id)

        for streaming_id in all_streamings_ids:
            summary[streaming_id] = await self.get_summary_by_streaming(streaming_id)

        return summary


    async def get_by_streaming(self, streaming_id: int) -> List[Optional[MovieDTO]]:
        
        movies = await self._repository.get_by_streaming(streaming_id)
        return [MovieDTO.model_dump(movie) if movie else None for movie in movies]


    async def get_by_id(self, movie_id: int) -> MovieDTO | None:
        return await self._repository.get_by_id(movie_id)

    async def get_by_title(self, title: str) -> MovieDTO | None:
        return await self._repository.get_by_title(title)

    async def get_by_language(self, language: str) -> Iterable[Movie]:
        return await self._repository.get_by_language(language)

    async def get_by_runtime(self, runtime: int) -> Iterable[Movie]:
        return await self._repository.get_by_runtime(runtime)


    async def filter_by_runtime(self, runtime_start: int, runtime_stop: int) -> Iterable[Movie]:
        return await self._repository.filter_by_runtime(runtime_start, runtime_stop)


    async def get_by_user(self, user_name: str) -> Iterable[Movie]:
        return await self._repository.get_by_user(user_name)

    async def add_movie(self, data: MovieIn) -> Movie | None:
        return await self._repository.add_movie(data)

    async def update_movie(self, movie_id: int, data: MovieIn) -> Movie | None:
        return await self._repository.update_movie(movie_id, data)

    async def delete_movie(self, movie_id: int) -> bool:
        return await self._repository.delete_movie(movie_id)