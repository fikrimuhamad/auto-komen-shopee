@echo off
echo ffmpeg streamer batch edition by hendyanwilly
set /p videoFile=Video name (example: video.mp4)? 
set /p rtmp=RTMP URL? 
set /p streamKey=Stream key? 
ffmpeg -re -stream_loop -1 -i "%videoFile%" -r 60 -b:v 1500k -c:v libx264 -preset veryfast -c:a aac -f flv "%rtmp%%streamKey%"
pause
