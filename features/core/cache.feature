@cache
Feature: Cache

  Scenario: Put router to cache and restore it later
    Given there is an empty router

    When I add URL "/" with method GET for route home
      And I add URL "/article-{{ id :uint }}" with method GET for route showArticle
      And I save router to cache
      And I restore router from cache

    Then router should allow for methods "HEAD, GET" for URL "/"
      And router should return showArticle '{"id": 5}' for HEAD "/article-5"
      And router should return showArticle '{"id": 6}' for GET "/article-6"
