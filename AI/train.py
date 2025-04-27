from ultralytics import YOLO

# Load a model
model = YOLO("yolo11n-cls.pt")  # load a pretrained model (recommended for training)

# Train the model

if __name__ == '__main__':
    results = model.train(data="C:\IVE\FYP\Development\Classification\dataset", epochs=300, imgsz=640)