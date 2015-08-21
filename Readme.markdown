Forked from Openphoto/Trovebox. As the commercial activities around this seem to have failed and are about to be shut down, and seeing that I use this code myself and thus would like to keep it alive, forking seemed to be no more than prudent.

Changes
=======

Apart from a bunch of bug fixes, the main difference between this fork and the original repo are:

 * PostgreSQL support
   The code in this repo has actually ONLY been tested with PostgreSQL. As there were quite a few MySQL-isms in the code, porting it to PostgreSQL went beyond merely adding a page to the database abstraction thingamajig. Due to the nature of the database abstraction layer and the peculiar way MySQL handles character case in database table and column names (case-insensitive but case-preserving), the code was full of camelCased array keys which map one-on-one with column names in the database. PostgreSQL lower-cases column names unless they are always enclosed in "double quotes". While it would be possible to use those quotes anywhere a column name is mentioned, this is tedious and emounts to useless extra work. The easiest solution was to replace all those camelCased column (and with that, array key) identifiers with lower_case_underscored versions. So said, so done, and the result works fine with PostgreSQL. I have not tested it with MySQL yet, but in theory it should work... 
 * ... (more in the pipeline)...

Some 'new' features I'd like to add but have not done so yet are:

 * better/working media support (where 'media' is anything from video to audio to PDF to whatever)
 * integration with Owncloud - maybe this could be implemeneted as an Owncloud app, replacing the rather restricted gallery app
 * some usable photo editing options
 * better tagging (something like f-spot tags, drag/drop
 * hierarchical tags?
 * hierarchical albums?
 * desktop integration to enable faster photo management of large library? Maybe integrate with an existing photo management app like Digikam, Darktable or even (the ghost of) F-spot?
 * Better performance on large libraries (partly solved with the use of PostgreSQL)

Be warned, though... 
