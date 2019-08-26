FROM ctftraining/base_image_xssbot

LABEL Organization="CTFTraining <http://github.com/CTFTraining>" Author="Virink <virink@outlook.com>"
MAINTAINER Virink@CTFTraining <virink@outlook.com>

COPY ./app.js /home/bot/app.js