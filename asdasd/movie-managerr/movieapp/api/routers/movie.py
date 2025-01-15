from typing import Iterable, Any
from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from jose import jwt

from movieapp.infrastructure.utils import consts
from movieapp.container import Container
from movieapp.core.domain.movie import Movie, MovieIn, MovieBroker # Zmienione importy
from movieapp.infrastructure.dto.moviedto import MovieDTO  # Dodany import
from movieapp.infrastructure.services.imovie import IMovieService # Zmieniony import


bearer_scheme = HTTPBearer()
router = APIRouter()


@router.post("/create", response_model=Movie, status_code=201)
@inject
async def create_movie(
    movie: MovieIn,
    service: IMovieService = Depends(Provide[Container.movie_service]),  # Zmieniony typ
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


    extended_movie_data = MovieBroker( # Zmieniona nazwa klasy
        user_id=user_uuid, #dodane
        **movie.model_dump(),
    )
    new_movie = await service.add_movie(extended_movie_data) #zmienione nazwy
    return new_movie.model_dump() if new_movie else {}


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

    if existing_movie := await service.get_by_id(movie_id):
        if str(existing_movie.user_id) != user_uuid:
            raise HTTPException(status_code=403, detail="Unauthorized to modify this movie")

        extended_movie_data = MovieBroker(
            user_id=user_uuid,
            **updated_movie.model_dump(),
        )
        updated_movie_data = await service.update_movie(
            movie_id=movie_id,
            data=extended_movie_data,
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

    if existing_movie := await service.get_by_id(movie_id):
        if str(existing_movie.user_id) != user_uuid:
            raise HTTPException(status_code=403, detail="Unauthorized to delete this movie.")

        await service.delete_movie(movie_id)
        return

    raise HTTPException(status_code=404, detail="Movie not found")



@router.get("/all", response_model=Iterable[MovieDTO], status_code=200) #zmieniony model odpowiedzi i nazwa metody
@inject
async def get_all_movies(

    service: IMovieService = Depends(Provide[Container.movie_service]), #zmieniony typ

) -> Iterable:



    movies = await service.get_all()


    return movies






@router.get("/{movie_id}", response_model=MovieDTO, status_code=200) # MovieDTO # Zmieniona nazwa metody i dodana adnotacja typu
@inject
async def get_movie_by_id(
    movie_id: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> dict | None:



    if movie := await service.get_by_id(movie_id):

        return movie.model_dump()

    raise HTTPException(status_code=404, detail="Movie not found") # Movie



@router.get("/user/{user_id}", response_model=list[Movie], status_code=200)
@inject
async def get_movies_by_user(
    user_id, #user_id
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:
    movies = await service.get_by_user(user_id)

    return movies





@router.get("/title/{title}", response_model=MovieDTO, status_code=200)  # Dodana adnotacja typu
@inject
async def get_movie_by_title(

    title: str,
    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> dict | None:

    if movie := await service.get_by_title(title): #get by title

        return movie.model_dump()



    raise HTTPException(status_code=404, detail="Movie not found")


@router.get(

        "/language/{language}", response_model=Iterable[Movie], status_code=200


)

@inject
async def get_movies_by_language(


    language: str,

    service: IMovieService = Depends(Provide[Container.movie_service]),
) -> Iterable:


    movies = await service.get_by_language(language)

    return movies


@router.get("/genre/{genre}", response_model=Iterable[Movie], status_code=200) # Dodana adnotacja typu
@inject
async def get_movies_by_genre(

    genre: str,

    service: IMovieService = Depends(Provide[Container.movie_service]),


) -> Iterable:


    movies = await service.get_by_genre(genre)


    return movies


@router.get("/runtime/{runtime}", response_model=Iterable[Movie], status_code=200)
@inject
async def get_movies_by_runtime(

    runtime: int,

    service: IMovieService = Depends(Provide[Container.movie_service]), #zmieniony typ

) -> Iterable:
    movies = await service.get_by_runtime(runtime) #get by runtime

    return movies




@router.get("/runtime/filter/{filter}", response_model=Iterable[Movie], status_code=200)  # Dodana adnotacja typu

@inject

async def filter_movies_by_runtime(

    runtime_start: int,
    runtime_stop: int,
    service: IMovieService = Depends(Provide[Container.movie_service]),

) -> Iterable:

    movies = await service.filter_by_runtime(runtime_start, runtime_stop) # Zmieniona nazwa metody



    return movies


@router.get("/streaming/{streaming}", response_model=Iterable[Movie], status_code=200)  # Dodana adnotacja typu
@inject
async def get_movie_by_streaming(
    streaming: str,
    service: IMovieService = Depends(Provide[Container.movie_service]), #zmieniony typ
) -> Iterable[Movie]:  # Zmieniona adnotacja typu

    movie = await service.get_by_streaming(streaming)
    return movie