DigiComp.League.Flysystem
-------------------------


This package contains a factory for the filesystem abstraction layer written by Frank de Jonge. 
See http://flysystem.thephpleague.com/

To use a filesystem in your project, I suggest you create an interface:

	/**
	 * @method array listFiles(string $path = '', boolean $recursive = false)
     * @method array listPaths(string $path = '', boolean $recursive = false)
	 */
    interface MyFilesystemInterface extends FilesystemInterface {}

And configure it with your Objects.yaml:

    AcMe\Package\SourceFilesystemInterface:
      scope: singleton
      factoryObjectName: DigiComp\League\Flysystem\FilesystemFactory
      factoryMethodName: create
      arguments:
        1:
          setting: 'AcMe.Package.filesystem'
        2:
          value: ["ListPaths", "ListFiles"]

While your Settings.yaml could look like this:

	AcMe:
	  Package:
		filesystem:
		  adapter: League\Flysystem\Adapter\Ftp
		  config:
			host: digital-competence.de
			username: user
			password: password
			root: /path/for/root
			
The array used as first parameter for the factory expects a "adapter" - key and all constructor arguments needed for the
  instantiation of the configured adapter. All other keys will be treated as property setters.
   
The second parameter is a list of plugins, which should be added to your filesystem. Without a package prefix, they will
be searched in League\Flysysten\Plugin. If you write something like "AcMe.Package:MyPlugin" the factory will look for an
plugin named \AcMe\Package\FlysystemPlugin\MyPlugin.

After that you can Flow let inject your Filesystem for you:

    /**
     * @var AcMe\Package\MyFilesystemInterface
     * @Flow\Inject
     */
     
And: Don't forget to have a lot of fun.