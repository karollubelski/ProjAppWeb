from contextlib import asynccontextmanager
from typing import AsyncGenerator

from fastapi import FastAPI, HTTPException, Request, Response
from fastapi.exception_handlers import http_exception_handler

from movieapp.api.routers.movie import router as movie_router
from movieapp.api.routers.watched import router as watched_router
from movieapp.api.routers.towatch import router as towatch_router
from movieapp.api.routers.user import router as user_router
from movieapp.container import Container
from movieapp.db import database
from movieapp.db import init_db


container = Container()
container.wire(modules=[
    "movieapp.api.routers.movie",
    "movieapp.api.routers.user",
    "movieapp.api.routers.watched",
    "movieapp.api.routers.towatch",
])


@asynccontextmanager
async def lifespan(_: FastAPI) -> AsyncGenerator:
    await init_db()
    await database.connect()
    yield
    await database.disconnect()


app = FastAPI(lifespan=lifespan)
app.include_router(movie_router, prefix="/movie")
app.include_router(watched_router, prefix="/watched")
app.include_router(towatch_router, prefix="/towatch")
app.include_router(user_router, prefix="")


@app.exception_handler(HTTPException)
async def http_exception_handle_logging(
    request: Request,
    exception: HTTPException,
) -> Response:

    return await http_exception_handler(request, exception)