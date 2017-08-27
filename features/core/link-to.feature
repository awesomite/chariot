@link-to
Feature: Generating links

  Scenario: Try generate correct and incorrect links
    Given there is an empty router

    When I add URL "/" with method GET for route home
      And I add URL "/categories/{{ categoryId :uint }}" with method GET for route categoryPage
      And I add URL "/edit-article/{{ articleId :uint }}" with method POST for route editArticle

    Then router should not generate URL for method POST with handler home
      And router should generate URL "/" for method HEAD with handler home
      And router should generate URL "/categories/15" for method GET with handler categoryPage and params '{"categoryId": 15}'
      And router should not generate URL for method "GET" with handler "categoryPage" and params '{"categoryId": "text value"}'
      And router should not generate URL for method GET with handler categoryPage
      And router should not generate URL for method GET with handler editArticle and params '{"articleId": 15}'
      And router should generate URL "/edit-article/15" for method POST with handler editArticle and params '{"articleId": 15}'
    
  Scenario: Additional parameters
    Given there is an empty router
    
    When I add URL "/" with method GET for route home
    
    Then router should generate URL "/?foo=bar" for method GET with handler home and params '{"foo": "bar"}'
      And router should generate URL "/" for method GET with handler home
