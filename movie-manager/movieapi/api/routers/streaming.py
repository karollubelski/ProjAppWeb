from typing import Iterable
from dependency_injector.wiring import inject, Provide
from fastapi import APIRouter, Depends, HTTPException

from movieapi.container import Container
from movieapi.core.domain.location import StreamingPlatform, StreamingPlatformIn
from movieapi.infrastructure.services.istreaming import IStreamingService

router = APIRouter()


@router.post("/create", response_model=StreamingPlatform, status_code=201)
@inject
async def create_streaming(
    streaming: StreamingPlatformIn,
    service: IStreamingService = Depends(Provide[Container.streaming_service]),
) -> dict:
    
    new_streaming = await service.add_streaming(streaming)

    return new_streaming.model_dump() if new_streaming else {}


@router.put("/{streaming_id}", response_model=StreamingPlatform, status_code=201)
@inject
async def update_streaming(
    streaming_id: int,
    updated_streaming: StreamingPlatformIn,
    service: IStreamingService = Depends(Provide[Container.streaming_service]),
) -> dict:
    
    if await service.get_streaming_by_id(streaming_id=streaming_id):

        new_updated_streaming = await service.update_streaming(
            streaming_id=streaming_id,
            data=updated_streaming,
        )
        return new_updated_streaming.model_dump() if new_updated_streaming else {}

    raise HTTPException(status_code=404, detail="Streaming platform not found")



@router.delete("/{streaming_id}", status_code=204)
@inject
async def delete_streaming(
    streaming_id: int,
    service: IStreamingService = Depends(Provide[Container.streaming_service]),
) -> None:
    
    if await service.get_streaming_by_id(streaming_id=streaming_id):

        await service.delete_streaming(streaming_id)
        return

    raise HTTPException(status_code=404, detail="Streaming platform not found")


@router.get("/all", response_model=Iterable[StreamingPlatform], status_code=200)
@inject
async def get_all_streamings(
    service: IStreamingService = Depends(Provide[Container.streaming_service]),
) -> Iterable:
    
    streamings = await service.get_all_streamings()

    return streamings


@router.get("/{streaming_id}", response_model=StreamingPlatform, status_code=200)
@inject
async def get_streaming_by_id(
    streaming_id: int,
    service: IStreamingService = Depends(Provide[Container.streaming_service]),
) -> dict:
    
    if streaming := await service.get_streaming_by_id(streaming_id):
        return streaming.model_dump()
    
    raise HTTPException(status_code=404, detail="Streaming platform not found")
