from typing import Optional, Iterable, Any, List
from datetime import date

from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from jose import jwt

from movieapi.infrastructure.utils import consts
from movieapi.container import Container
from movieapi.core.domain.movie import Movie, MovieIn, MovieBroker
from movieapi.infrastructure.dto.moviedto import MovieDTO
from movieapi.infrastructure.services.imovie import IMovieService


bearer_scheme = HTTPBearer()
router = APIRouter()


@router.post("/create", response_model=MovieDTO, status_code=201) # Zmienione
@inject
async def create_movie(
    movie: MovieIn,
    service: IMovieService = Depends(Provide[Container.movie_service]),
    credentials: HTTPAuthorizationCredentials = Depends(bearer_scheme),
) -> dict | None:

    token = credentials.credentials
    token_payload = jwt.decode(
        token,
        key=consts.SECRET_KEY,
        algorithms=[consts.ALGORITHM],
    )
    user_uuid = token_payload.get("sub")


    if not user_uuid:
        raise HTTPException(status_code=403, detail="Unauthorized")

    extended_movie_data = MovieBroker(
        user_id=user_uuid,
        **movie.model_dump(),
    )


    new_movie = await service.add_movie(extended_movie_data)
    if not new_movie:
      raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST, detail="Failed to create movie"
        )


    new_movie_dto = await service.get_by_id(new_movie.id)
    return new_movie_dto.model_dump()



@router.put("/{movie_id}", response_model=Movie, status_code=201)
@inject
async def update_movie(
    movie_id: int,
    updated_movie: MovieIn,
    service: IMovieService = Depends(Provide[Container.movie_service]),
    credentials: HTTPAuthorizationCredentials = Depends(bearer_scheme),
) -> dict:

    token = credentials.credentials
    token_payload = jwt.decode(
        token,
        key=consts.SECRET_KEY,
        algorithms=[consts.ALGORITHM],
    )
    user_uuid = token_payload.get("sub")

    if not user_uuid:
        raise HTTPException(status_code=403, detail="Unauthorized")

    if movie_data := await service.get_by_id(movie_id=movie_id):
        if str(movie_data.user_id) != user_uuid:
            raise HTTPException(status_code=403, detail="Unauthorized")

        extended_updated_movie = MovieBroker(
            user_id=user_uuid,
            **updated_movie.model_dump(),
        )


        updated_movie_data = await service.update_movie(
            movie_id=movie_id,
            data=extended_updated_movie,
        )
        return updated_movie_data.model_dump() if updated_movie_data else {}

    raise HTTPException(status_code=404, detail="Movie not found")




@router.delete("/{movie_id}", status_code=204)
@inject
async def delete_movie(
    movie_id: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
    credentials: HTTPAuthorizationCredentials = Depends(bearer_scheme),
) -> None:

    token = credentials.credentials
    token_payload = jwt.decode(
        token,
        key=consts.SECRET_KEY,
        algorithms=[consts.ALGORITHM],
    )
    user_uuid = token_payload.get("sub")

    if not user_uuid:
        raise HTTPException(status_code=403, detail="Unauthorized")

    if movie_data := await service.get_by_id(movie_id=movie_id):
        if str(movie_data.user_id) != user_uuid:
            raise HTTPException(status_code=403, detail="Unauthorized")
        await service.delete_movie(movie_id)
        return

    raise HTTPException(status_code=404, detail="Movie not found")


@router.get("/all", response_model=Iterable[MovieDTO], status_code=200)
@inject
async def get_all_movies(
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:
    
    movies = await service.get_all()
    return movies

# @router.get("/all", response_model=List[MovieDTO], status_code=200)
# @inject
# async def get_all_movies(
#     service: IMovieService = Depends(Provide[Container.movie_service]),
# ) -> List[MovieDTO]:
    
#     movies = await service.get_all()
#     return movies



@router.get("/streaming/summary/by", response_model=Any, status_code=200)
@inject
async def get_summary_by_streaming(
    streaming_id: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:
    
    summary = await service.get_summary_by_streaming(streaming_id)
    return summary


@router.get("/streaming/summary/all", response_model=Any, status_code=200)
@inject
async def get_summary_by_all_streamings(
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:
    
    summary = await service.get_summary_by_all_streamings()
    return summary



@router.get("/streaming/{streaming_id}", response_model=List[MovieDTO], status_code=200)
@inject
async def get_movies_by_streaming(
    streaming_id: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> List[MovieDTO] | None:
    
    movies = await service.get_by_streaming(streaming_id)

    return movies



@router.get("/{movie_id}", response_model=MovieDTO, status_code=200)
@inject
async def get_movie_by_id(
    movie_id: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Optional[dict]:

    if movie := await service.get_by_id(movie_id):
        return movie.model_dump()

    raise HTTPException(status_code=404, detail="Movie not found")



@router.get("/user/{user_name}", response_model=List[MovieDTO], status_code=200)
@inject
async def get_movies_by_user(
    user_name: str, 
    service: IMovieService = Depends(Provide[Container.movie_service])
) -> List[MovieDTO]:
     
    movies = await service.get_by_user(user_name)
    return [MovieDTO.model_dump(m) if m else None for m in movies ]


@router.get("/title/{title}", response_model=List[MovieDTO], status_code=200)
@inject
async def get_movies_by_title(
    title: str,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> List[MovieDTO] | None:
    
    movies = await service.get_by_title(title)
    if movies==None:

        raise HTTPException(status_code=404, detail="Movies not found")

    return movies



@router.get("/language/{language}", response_model=Iterable[Movie], status_code=200)
@inject
async def get_movies_by_language(
    language: str,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:
    
    movies = await service.get_by_language(language)
    return movies



@router.get("/runtime/{runtime}", response_model=List[MovieDTO], status_code=200)
@inject
async def get_movies_by_runtime(
    runtime: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:
    
    movies = await service.get_by_runtime(runtime)
    return [MovieDTO.model_dump(movie) for movie in movies]


@router.get("/runtime/filter/{runtime_start}/{runtime_stop}", response_model=Iterable[Movie], status_code=200)
@inject
async def filter_movies_by_runtime(
    runtime_start: int,
    runtime_stop: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:

    movies = await service.filter_by_runtime(runtime_start, runtime_stop)
    return movies


