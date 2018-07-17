# Introduction

CiviHR is a complex system, with many moving parts. The code in this repository is just one of these parts. It's possible to create a new site manually, but, given the number of necessary steps, this a hard and time consuming task. For this reason, the best way to install CiviHR is by using one of the installation methods available: 

## Using the civihr-installer script

The [civihr-installer](https://github.com/compucorp/civihr-installer) script has a mininum set of requirements and it is a quick way to get CiviHR up and running. Please check its repository for more information on how to use it.

> Note: A site created with this script is not suited for development, as it will not include many tools and files required for this kind of work. If you're looking for that, the installation with buildkit is the way to go. 

## Using buildkit

[Buildkit](https://github.com/civicrm/civicrm-buildkit) is a collection of development tools for CiviCRM. Among these tools, there's `civibuild`, which can be used to quickly create new sites. To create a new CiviHR site, you can use the `hr17` build type:

```
$ civibuild create hr17
```

Please check the [civibuild documentation](https://docs.civicrm.org/dev/en/latest/tools/civibuild/) for more information on all the available params. 
