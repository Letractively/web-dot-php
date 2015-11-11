# Active Record #

web.php doesn't have an [active record](http://en.wikipedia.org/wiki/Active_record) implementation "out of the box". Active record implementation is definitely not on web.php todo list. It's not that we don't like active record, but rather because there is already many great projects that have implemented active record for PHP. You can use them with web.php, if you want to. Our mission is not to rewrite everything out there, but focus on things that we don't like in other frameworks, libraries or tools.

## Active Record Frameworks ##

### Doctrine (http://www.doctrine-project.org/) ###

> [Doctrine](http://www.phpdoctrine.net/) is an ORM (object relational mapper) for PHP 5.2.x+ that sits on top of a powerful DBAL (database abstraction layer). One of its key features is the ability to optionally write database queries in an OO (object oriented) SQL-dialect called DQL inspired by Hibernates HQL. This provides developers with a powerful alternative to SQL that maintains a maximum of flexibility without requiring needless code duplication.

We think that Doctrine is the perfect solution for PHP developer's active record needs. You should look no further, if you ask us.

### Propel (http://propel.phpdb.org/) ###

> [Propel](http://propel.phpdb.org/) is an Object-Relational Mapping (ORM) framework for PHP5. It allows you to access your database using a set of objects, providing a simple API for storing and retrieving data.

Propel is not actually an implementation of active record pattern, but it's a rather good object relation mapping framework that is driven with simple XML configuration. Propel is also one of the first open source ORM frameworks for PHP, and it has been in development for many years. That's what makes it very stable and mature.

### RedBeanPHP (http://redbeanphp.com/) ###

> Start developing using the easiest ORM layer ever made! With RedBean, ORM (Object Relational Mapping) in PHP becomes a breeze. RedBeanPHP is a simple straightfoward, lightweight ORM solution; with a 'fire and forget' philosophy!

[RedBeanPHP](http://redbeanphp.com/) is really interesting project. I think that RedBeanPHP is a little bit more original than typical Active Record implementing ORMs. If you are looking for something fresh, take a look at RedBeanPHP.