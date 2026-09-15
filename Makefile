.PHONY: start

start:
	docker compose \
		up \
		--always-recreate-deps \
		--build \
		--force-recreate \
		--remove-orphans

connect:
	docker exec -ti greenpro-dev-1 bash

