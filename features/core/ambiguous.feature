@ambiguous
Feature: Ambiguous cases

  Scenario: Different routes for different methods
    Given there is an empty router
    
    When I add URL "/article-{{ id :uint }}" with method GET for route showArticle
      And I add URL "/article-{{ id :uint }}" with method DELETE for route deleteArticle
    
    Then router should allow for methods "HEAD, GET, DELETE" for URL "/article-5"
      And router should return showArticle '{"id": 5}' for HEAD "/article-5"
      And router should return showArticle '{"id": 6}' for GET "/article-6"
      And router should return deleteArticle '{"id": 7}' for DELETE "/article-7"
    
  Scenario: Two matching routes (first contains regex, second does not contain regex)
    Given there is an empty router
    
    When I add URL "/pages/{{ page }}" with method GET for route showPage
      And I add URL "/pages/contact" with method GET for route showContact
    
    Then router should allow for methods "GET, HEAD" for URL "/pages/contact"
      And router should return "showContact" "{}" for GET "/pages/contact"
    
  Scenario: Two matching routes (both contain regex)
    Given there is an empty router
    
    When I add URL "/page/{{ title }}" with method GET for route byTitle
      And I add URL "/page/{{ id :uint }}" with method GET for route byId
    
    Then router should allow for methods "GET, HEAD" for URL "/page/123"
      And router should return byTitle '{"title": "123"}' for GET "/page/123"
    
  Scenario: Generate link with hidden parameters
    Given there is an empty router
    
    When I add URL "/page" with method GET for route showPage '{"page": 1}'
      And I add URL "/page-{{ page :uint }}" with method GET for route showPage
    
    Then router should generate URL "/page" for method GET with handler showPage and params '{"page": 1}'
      And router should return showPage '{"page": 1}' for GET "/page"
      And router should generate URL "/page-2" for method GET with handler showPage and params '{"page": 2}'
