#!/bin/bash

cd ~/Desktop/CardCMS

php -S localhost:8000 &
sleep 2

open http://localhost:8000/admin/index.php

wait
