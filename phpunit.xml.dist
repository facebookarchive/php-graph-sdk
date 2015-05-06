<phpunit
        colors="true"
        stderr="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        stopOnFailure="false"
        bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Facebook PHP SDK Test Suite">
            <directory>./tests</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory suffix=".php">./src/Facebook</directory>
        </whitelist>
    </filter>
    <listeners>
        <listener class="\Mockery\Adapter\Phpunit\TestListener"/>
    </listeners>
</phpunit>
