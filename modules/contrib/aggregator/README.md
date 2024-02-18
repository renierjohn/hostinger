# Aggregator

Use the Aggregator module to ingest content from RSS feeds into your site. This
is the same module that was in Drupal core until version 10.0. It was deprecated
in Drupal core version 9.4 and moved to contrib. It is used to power
[Drupal planet](https://www.drupal.org/planet) on
[Drupal.org](https://drupal.org).


## Requirements

No other modules are required.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-modules).


## Configuration

1. Enable the module under Administration > Extend
1. Add and manage feeds at Administration > Configuration > Web services >
   Aggregator (/admin/config/services/aggregator)
1. Configure settings for all feeds at Administration > Configuration > Web
   services > Aggregator (/admin/config/services/aggregator/settings)

It is recommended to only grant the "Administer news feeds" permission to
trusted user roles. Aggregator may be used to perform some low-threat security
attacks against the site host or other servers on the same network. For
example:
* Feed entities may be created that perform server-side request forgery (SSRF)
  requests against the host, permitting scanning of localhost ports.
* If a host is behind a firewall on a private network, then feeds from sites
  behind that same firewall may be created, for instance from an intranet RSS
  feed.  This would expose the feed to the public Internet.

The potential threats are not severe enough to warrant
limiting the URLs that Aggregator will fetch, but caution should be exercised
when permitting users to create feeds.


## Maintainers

- Lee Rowlands - [larowlan](https://www.drupal.org/u/larowlan)
- David Cameron - [dcam](https://www.drupal.org/u/dcam)
