#In case of scale the app to 2 instances
# docker-compose up -d --build --scale app=2

#build with no cache
docker-compose build --no-cache
docker-compose up -d