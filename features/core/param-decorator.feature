@decorator
Feature: Param decorator

  Scenario: Transform parameters
    Given there is an empty router

    When I add URL "/items/{{ id :uint }}-{{ name }}" with method GET for route showItem
      And I add decorator LowerCaseDecorator for param "name"

    Then router should generate URL "/items/5-my-item" for method GET with handler showItem and params '{"id": 5, "name": "My-Item"}'

    When I save router to cache
      And I restore router from cache

    Then router should generate URL "/items/5-My-Item" for method GET with handler showItem and params '{"id": 5, "name": "My-Item"}'

    When I add decorator LowerCaseDecorator for param "name"

    Then router should generate URL "/items/5-my-item" for method GET with handler showItem and params '{"id": 5, "name": "My-Item"}'
