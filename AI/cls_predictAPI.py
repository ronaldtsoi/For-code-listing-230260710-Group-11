from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from ultralytics import YOLO
import cv2
import numpy as np
import base64
#import gc
import uvicorn

app = FastAPI()

# 載入 YOLOv11 分類模型
model = YOLO("helmetCLS.pt")

class ImageRequest(BaseModel):
    image: str  # base64 encoded image

def decode_image(image_data: str):
    image_bytes = base64.b64decode(image_data)
    nparr = np.frombuffer(image_bytes, np.uint8)
    return cv2.imdecode(nparr, cv2.IMREAD_COLOR)

@app.post("/classify")
async def classify(data: ImageRequest):
    try:
        image = decode_image(data.image)

        # 推論（分類）
        result = model(image)[0]
        label = model.names[result.probs.top1]  # 類別名稱

        del result
        #gc.collect()

        return  {"label": label}

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=5000, log_level="info")
