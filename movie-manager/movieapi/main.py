from contextlib import asynccontextmanager
from typing import AsyncGenerator

from fastapi import FastAPI, HTTPException, Request, Response
from fastapi.exception_handlers import http_exception_handler

from movieapi.api.routers.movie import router as movie_router
from movieapi.api.routers.streaming import router as streaming_router
from movieapi.api.routers.watched import router as watched_router
from movieapi.api.routers.towatch import router as towatch_router
from movieapi.api.routers.user import router as user_router
from movieapi.container import Container
from movieapi.db import database
from movieapi.db import init_db


container = Container()
container.wire(modules=[
    "movieapi.api.routers.streaming",
    "movieapi.api.routers.user",
    "movieapi.api.routers.movie",
    "movieapi.api.routers.watched",
    "movieapi.api.routers.towatch",
])


@asynccontextmanager
async def lifespan(_: FastAPI) -> AsyncGenerator:
    await init_db()
    await database.connect()
    yield
    await database.disconnect()


app = FastAPI(lifespan=lifespan)

app.include_router(movie_router, prefix="/movie")
app.include_router(streaming_router, prefix="/streaming")
app.include_router(watched_router, prefix="/watched")
app.include_router(towatch_router, prefix="/towatch")
app.include_router(user_router, prefix="")



@app.exception_handler(HTTPException)
async def http_exception_handle_logging(request: Request, exception: HTTPException) -> Response:
    return await http_exception_handler(request, exception)