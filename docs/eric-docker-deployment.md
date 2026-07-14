# Eric Grocy Docker Deployment

This repository builds a personal Grocy image on Gitea Actions.

## Version Rule

Use the upstream Grocy version plus an Eric customization revision:

```text
v<grocy-version>-eric.<revision>
```

Current version:

```text
v4.6.0-eric.1
```

When upstream Grocy is updated to a new version, reset or continue the Eric revision intentionally, for example:

```text
v4.7.0-eric.1
v4.7.0-eric.2
```

`version.json` keeps the upstream Grocy version. `eric-version.json` records the custom build version.

## Gitea Actions

The workflow is `.gitea/workflows/release-image.yml`.

It runs only when pushing tags matching:

```text
v*-eric.*
```

For the current base version, the workflow validates tags as:

```text
v4.6.0-eric.N
```

Required repository secrets:

```text
REGISTRY_USER
REGISTRY_PASSWORD
```

The default registry and image repository are configured in the workflow:

```text
192.168.200.101:54453/zhangzhicheng/eric-grocy
```

## Build Trigger

```bash
git tag v4.6.0-eric.1
git push origin eric/custom-shopping-flow
git push origin v4.6.0-eric.1
```

The workflow pushes:

```text
192.168.200.101:54453/zhangzhicheng/eric-grocy:v4.6.0-eric.1
192.168.200.101:54453/zhangzhicheng/eric-grocy:latest
192.168.200.101:54453/zhangzhicheng/eric-grocy:sha-<commit>
```

## Deploy

Create `.env` from `.env.docker.example`, then run:

```bash
docker compose pull
docker compose up -d
```

Default URL:

```text
http://<host>:9284/
```

The container stores Grocy data in:

```text
/var/www/html/data
```

The compose file maps it to:

```text
./data
```

On first startup, the entrypoint creates a minimal `config.php` if it does not exist. Grocy settings can then be controlled through `GROCY_*` environment variables or files under `data/settingoverrides`.
