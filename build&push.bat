@ECHO OFF
docker build . -t omeka_s_flandrica
docker tag omeka_s_flandrica registry.docker.libis.be/omeka_s_flandrica
docker push registry.docker.libis.be/omeka_s_flandrica
ECHO Image built, tagged and pushed succesfully
PAUSE
