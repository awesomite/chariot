@custom
Feature: Custom patterns
  
  Scenario: Adding custom pattern
    Given there is an empty router
    
    When I add pattern "one|two|three" with name ":humanNumber"
      And I add URL "/show-page-{{ page :humanNumber }}" with method GET for route showPage
    
    Then router should return showPage '{"page": "one"}' for GET "/show-page-one"
      And router should return showPage '{"page": "two"}' for GET "/show-page-two"
      And router should return showPage '{"page": "three"}' for GET "/show-page-three"
      And router should throw 404 for GET "/show-page-four"
      And router should generate URL "/show-page-one" for method GET with handler showPage and params '{"page": "one"}'
      And router should not generate URL for method GET with handler showPage and params '{"page": "four"}'
    
    When I add pattern "\d{4}-\d{2}-\d{2}" with name ":simpleDate"
      And I add URL "/day/{{ date :simpleDate }}" with method GET for route showDay
    Then router should return showDay '{"date": "2020-01-01"}' for GET "/day/2020-01-01"
      And router should throw 404 for GET "/day/20-01-01"
