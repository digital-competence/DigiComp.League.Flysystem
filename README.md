DigiComp.League.Flysystem
-------------------------


This package contains a factory for the filesystem abstraction layer written by Frank de Jonge.
See http://flysystem.thephpleague.com/

There are two possibilities to use filesystems.

1.
    To use a filesystem in your project, I suggest you create an interface:

        /**
         * @method array listFiles(string $path = '', boolean $recursive = false)
         * @method array listPaths(string $path = '', boolean $recursive = false)
         */
        interface MyFilesystemInterface extends FilesystemInterface {}
    
    And configure it with your `Objects.yaml`:
    ```yaml 
    AcMe\Package\SourceFilesystemInterface:
      scope: 'singleton'
      factoryObjectName: 'DigiComp\League\Flysystem\FilesystemFactory'
      factoryMethodName: 'create'
      arguments:
        1:
          setting: 'AcMe.Package.filesystem'
        2:
          value: ['ListPaths', 'ListFiles']
    ```
    While your `Settings.yaml` could look like this:
    ```yaml
    AcMe:
      Package:
        filesystem:
          adapter: 'League\Flysystem\Adapter\Ftp'
          adapterArguments:
            config:
              host: 'digital-competence.de'
              username: 'user'
              password: 'password'
              root: '/path/for/root'
          filesystemConfig:
            visibility: public
            disable_asserts: true
   ```
    
    The array used as first parameter for the factory expects an "adapter" and allows a "filesystemConfig" - key and all 
    constructor arguments needed for the instantiation of the configured adapter. 
    
    All other keys will be treated as property setters for the adapter.
    
    The second parameter is a list of plugins, which should be added to your filesystem. Without a package prefix, they
    will be searched in `League\Flysystem\Plugin`. If you write something like `AcMe.Package:MyPlugin` the factory will
    look for a plugin named `\AcMe\Package\FlysystemPlugin\MyPlugin`.
    
    After that you can Flow let inject your filesystem for you:
    
        /**
         * @var AcMe\Package\MyFilesystemInterface
         * @Flow\Inject
         */
         
2. You could use a named Filesystem. Most of the parts above still makes sense, but instead of passing configuration 
     arrays to the factory you might configure it in a Filesystem.yaml and give it a name.
     ```yaml
     DigiComp.League.Flysystem.TestingFilesystem:
       adapter: 'League\Flysystem\Adapter\Local'
       adapterArguments:
         root: '%FLOW_PATH_PACKAGES%Application/DigiComp.League.Flysystem'
       filesystemConfig:
         visibility: 'public'
       plugins: ['ListPaths', 'ListFiles']
     ```
     And then in Objects.yaml use use a different factory method:
     ```yaml
     AcMe\Package\SourceFilesystemInterface:
       scope: 'singleton'
       factoryObjectName: 'DigiComp\League\Flysystem\FilesystemFactory'
       factoryMethodName: 'createNamedFilesystem'
       arguments:
         1:
           value: 'DigiComp.League.Flysystem.TestingFilesystem'
     ```

And: Don't forget to have a lot of fun.
