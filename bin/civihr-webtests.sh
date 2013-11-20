#!/bin/bash
##################################
## List of extensions defining basic entity types
ENTITY_EXTS=( hrbank \
hrcareer \
hrident \
hrjob \
hrmed \
hrqual \
hrstaffdir \
hrvisa \
)
##################################

for var in "${ENTITY_EXTS[@]}"
  do
    cd ../${var}
    set -ex
      civix test WebTest_AllTests
    set +ex
done