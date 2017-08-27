@match
Feature: Match request to route
  In order to convert URL to route

  Scenario: Matching URL for router with one route added
    Given there is an empty router
    
    When I add URL "/" with method GET for route home
    
    Then router should return home "{}" for GET "/"
      And router should throw 405 for POST "/"
      And router should allow for methods "HEAD, GET" for URL "/"
      And router should throw 404 for GET "/404"
      And router should allow for methods "" for URL "/404"
      And router should throw 404 for GET "/contact"
    
    When I add URL "/contact" with method "GET" for route contactPage
    
    Then router should return contactPage "{}" for GET "/contact"

  Scenario: Hidden params in URL
    Given there is an empty router
    
    When I add URL "/show-first-page" with method GET for route showPage '{"page": 1}'
      And I add URL "/show-second-page" with method GET for route showPage '{"page": 2}'
      And I add URL "/show-third-page" with method GET for route showPage '{"page": 3}'
    
    Then router should throw 405 for DELETE "/show-first-page"
      And router should return showPage '{"page": 1}' for HEAD "/show-first-page"
      And router should return showPage '{"page": 2}' for HEAD "/show-second-page"
      And router should return showPage '{"page": 3}' for HEAD "/show-third-page"
