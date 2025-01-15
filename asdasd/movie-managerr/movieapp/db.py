import asyncio

import databases
import sqlalchemy
from sqlalchemy.dialects.postgresql import UUID
from sqlalchemy.exc import OperationalError, DatabaseError
from sqlalchemy.ext.asyncio import create_async_engine
from asyncpg.exceptions import (  # type: ignore
    CannotConnectNowError,
    ConnectionDoesNotExistError,
)

from movieapp.config import config

metadata = sqlalchemy.MetaData()



watched_table = sqlalchemy.Table(
    "watched",
    metadata,
    sqlalchemy.Column("id", sqlalchemy.Integer, primary_key=True),
    sqlalchemy.Column("user_name", sqlalchemy.String),
    sqlalchemy.Column("movie_title", sqlalchemy.String), 
    sqlalchemy.Column("user_id", UUID(as_uuid=True)),


)


towatch_table = sqlalchemy.Table( 
    "towatch",
    metadata,
    sqlalchemy.Column("id", sqlalchemy.Integer, primary_key=True),
    sqlalchemy.Column("movie_title", sqlalchemy.String),
    sqlalchemy.Column("user_id", UUID(as_uuid=True)),


)



user_table = sqlalchemy.Table(
    "users",
    metadata,
    sqlalchemy.Column(
        "id",
        UUID(as_uuid=True),
        primary_key=True,
        server_default=sqlalchemy.text("gen_random_uuid()"),
    ),
    sqlalchemy.Column("name", sqlalchemy.String, unique=True),
    sqlalchemy.Column("email", sqlalchemy.String, unique=True),
    sqlalchemy.Column("password", sqlalchemy.String),
)

movie_table = sqlalchemy.Table( 
    "movies",
    metadata,
    sqlalchemy.Column("id", sqlalchemy.Integer, primary_key=True),
    sqlalchemy.Column("title", sqlalchemy.String), 
    sqlalchemy.Column("language", sqlalchemy.String),
    sqlalchemy.Column("description", sqlalchemy.String, nullable=True),
    sqlalchemy.Column("release_date", sqlalchemy.Date, nullable=True),
    sqlalchemy.Column("genres", sqlalchemy.ARRAY(sqlalchemy.String)), 
    sqlalchemy.Column("runtime", sqlalchemy.Integer), 
    sqlalchemy.Column("streamings", sqlalchemy.ARRAY(sqlalchemy.String)),
    sqlalchemy.Column(
        "user_id",
        sqlalchemy.ForeignKey("users.id"),
        nullable=False,
    ),
    

)

db_uri = (
    f"postgresql+asyncpg://{config.DB_USER}:{config.DB_PASSWORD}"
    f"@{config.DB_HOST}/{config.DB_NAME}"
)

engine = create_async_engine(
    db_uri,
    echo=True,
    future=True,
    pool_pre_ping=True,
)

database = databases.Database(
    db_uri,
    force_rollback=True,
)


async def init_db(retries: int = 5, delay: int = 5) -> None:
    """Function initializing the DB.

    Args:
        retries (int, optional): Number of retries of connect to DB.
            Defaults to 5.
        delay (int, optional): Delay of connect do DB. Defaults to 2.
    """
    for attempt in range(retries):
        try:
            async with engine.begin() as conn:
                await conn.run_sync(metadata.create_all)
            return
        except (
            OperationalError,
            DatabaseError,
            CannotConnectNowError,
            ConnectionDoesNotExistError,
        ) as e:
            print(f"Attempt {attempt + 1} failed: {e}")
            await asyncio.sleep(delay)

    raise ConnectionError("Could not connect to DB after several retries.")
