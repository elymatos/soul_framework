#!/bin/bash

./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=6
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=7
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=8
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=12
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=13
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=15
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=16
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=17
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=18
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=22
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=23
./sail.sh a export:xml-framework fulltext --output=fulltext --language=2 --id=24

./sail.sh a export:xml-framework corpus --output=fulltext --language=2 --id=1

./sail.sh a export:xml-framework frames --output=frame --language=2
./sail.sh a export:xml-framework lexunit --output=lu --language=2
./sail.sh a export:xml-framework frameIndex --language=2
./sail.sh a export:xml-framework frRelation --language=2
./sail.sh a export:xml-framework fulltextIndex --language=2
./sail.sh a export:xml-framework luIndex --language=2
./sail.sh a export:xml-framework semTypes --language=2


