require 'selenium-webdriver'
require 'rspec/expectations'
include RSpec::Matchers

def setup
    caps = Selenium::WebDriver::Remote::Capabilities.send("chrome")
    @driver = Selenium::WebDriver.for(:remote, url: "http://0.0.0.0:4444/wd/hub", desired_capabilities: caps)
    @driver.manage.window.size = Selenium::WebDriver::Dimension.new(1920, 1080)
end

def teardown
    @driver.quit
end

def run
    setup
    yield
    teardown
end

run do
    site_url = ARGV[0]

    puts "Running tests against WP install: #{site_url}"

    # Open the main page and check for the title
    @driver.get site_url + '/'
    @driver.save_screenshot(File.join(Dir.pwd, "selenium-docker-main-page.png"))
    expect(@driver.title).to eql 'wp plugindev – Just another WordPress site'

    puts 'Title test OK'

    @driver.get site_url + '/wp-login.php'
    expect(@driver.title).to eql 'wp plugindev ‹ Log In'

    @driver.save_screenshot(File.join(Dir.pwd, "selenium-docker-login-page.png"))

    #id user_login
    @driver.find_element(name: 'log').send_keys 'admin'
    @driver.find_element(name: 'pwd').send_keys 'admin'
    @driver.find_element(name: 'wp-submit').submit

    sleep 5

    expect(@driver.title).to eql 'Dashboard ‹ wp plugindev — WordPress'

    puts 'Login test OK'

    @driver.get site_url + '/wp-admin/tools.php?page=wp-static-html-output-options'

    # setup export and run

    # get list of files from export folder (should be only one exported folder)

    # check contents of index.html file

    ## Generate a screenshot of the checkbox page
    @driver.save_screenshot(File.join(Dir.pwd, "selenium-docker-plugin-settings.png"))
end
