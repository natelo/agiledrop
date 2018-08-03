Test Drupal module

Shortcomings    
-----------------------   
CSS is not loaded thus it was styled using "style" attribute inside twig template

- Seems like a simple routing/loading issue..I need some clarification..   
- Optionally it could be parsed with attributes in rendering array but that's kinda a hacky way of doing things..


----------------------- 
Visibility issue      
As part of the task, block should only be visible in Events.

Possible solutions and issues:

1) Existing solutions      
a) Visibility can be controlled via "configure block (Structure->..):    
- If set on "Content Type:Event" then it wont be shown in "Events" page   
b) If set on "Pages"         
- It would require manual editing of desirable outcome is "events/" & node-X (event viewing page)     
- It would only be visible on "events/" and any other desirable page (i.e. root|index/)  

2) Extending/Patching     
a) Patch can be applied that introduces OR argument for "configure block" visibility so configuration can be used in shape of "ContentType and Pages" filtering   
Patch: https://www.drupal.org/project/drupal/issues/923934#comment-12401360   
Downside: It breaks compatibility issues with future upgrades  
b) Extending functionality via existing plugins..      
i.e. https://www.drupal.org/project/block_visibility_conditions    
Downside: Creates dependency on external source as well as doesn't solve the understanding aspect of things


3) Creating filter via "../Plugins/Conditions/" route. Seems like the most reasonable and the right way.
Issue: I could not obtain any clear examples how to achieve it. Most of tutorials are either outdated or lacking any explanation what exactly is utilizing and more importantly, why. 
Albeit concept is simple: Utilize URL component and figure out if it matches ContentType or Alias (events/). Problem is lack of explanations what methods should be used/called and when.
