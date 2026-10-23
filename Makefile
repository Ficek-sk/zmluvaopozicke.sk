#!make

all:
	@echo "Project aliases and shortcuts."
	@echo "  make cc                            - Clear cache"

help:
	make all

cc:
	./bin/cleantemp.sh
