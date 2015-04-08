/**
 * Populates finalPostObjList
 * Adds excerpt node element to postList
 *
 * 1. Search for all coded excerpt nodes
 * 2. For each node, find the corresponding postObj:
 *    a. Iterate for a postObj whose 'content' attribute contains the coded_excerpt
 *    if there are none, or there are two:
 *    b. Search upward through parents of coded_excerpt until you find a single element with 'post-(id)' or '(nonnumeric)(id)'
 *    c. If there are two, check if one of them fits both categories (implement this later)
 */
function associatePostObjWithExcerptNode(postList) {
    var excerptNodes = getChildNodesWithCondition(document, function(childNode) {
        var data = childNode.data;
        return data && data.match && data.match(/[\+\-\*]{3}/g);
    });
    
    for(var i=0; i<excerptNodes.length; i++) {
        var excerptNode = excerptNodes[i];
        
        var foundPostObj = findCorrespondingPostObjById(excerptNode, postList);
        if(!foundPostObj) {
            foundPostObj = findCorrespondingPostObjByContent(excerptNode, postList);
        }
        if(foundPostObj) {
            foundPostObj.excerptNode = excerptNode;
            finalPostObjList.push(foundPostObj);
        } else {
            finalPostObjList.push({excerptNode: excerptNode});
        }
    }
}

/**
 * Iterates to find a postObj whose 'content' attribute overlaps excerptNode.data
 * Then associates the postObj with excerptNode
 */
function findCorrespondingPostObjByContent(excerptNode, postList) {
    var matchingPostObjs = [];
    
    for(var i=0; i<postList.length; i++) {
        var postObj = postList[i];
        var trimmedContent = postObj.content.trim();
        var trimmedData = excerptNode.data.trim();
        var longestCommonSubLen = longestCommonSubstring(
            trimmedContent,
            trimmedData
        );
        if(longestCommonSubLen / trimmedContent.length >= .90 ||
           longestCommonSubLen / trimmedData.length >= .90) {
            matchingPostObjs.push(postObj);
        }
    }
    //If we have a single matching postObj, associate it with the current excerptNode
    if(matchingPostObjs.length === 1) {
        return matchingPostObjs[0];
    }
    return null;
}

/**
 * Searches upward through parents of excerptNode to discover a
 *   single postNode with 'post-(id)' or '(nonnumeric)(id)'
 * Then associates the corresponding postObj with excerptNode
 */
function findCorrespondingPostObjById(excerptNode, postList) {
    var matchingPostObjs = [];
    var success = false;
    
    for(var k=0; k<postList.length; k++) {
        var postObj = postList[k];
        var conditionFunc = function(childNode) {
            var id = childNode.id;
            var idHasPostConvention = id === 'post-' + postObj.id;
            var idMatchesSomewhat = id && id.match && id.match(new RegExp('^[0-9]+' + postObj.id, 'g'));
            return idHasPostConvention || idMatchesSomewhat;
        };
        
        //If we found a matching postNode, associate the current postObj
        //  with the current excerptNode
        var firstElder = getFirstElderWithCondition(excerptNode, conditionFunc);
        if(firstElder) {
            matchingPostObjs.push({postObj: postObj, pos: firstElder.pos});
        }
    }
    var candidatePos;
    var closestObj;
    for(var i=0; i<matchingPostObjs.length; i++) {
        var matchingPostObj = matchingPostObjs[i];
        if(!candidatePos || candidatePos > matchingPostObj.pos) {
            candidatePos = matchingPostObj.pos;
            closestObj = matchingPostObj.postObj;
        }
    }
    return closestObj;
}

/**
 * Halts on first success
 */
var nodeMarker = 0;
function getFirstElderWithCondition(node, conditionFunc) {
    nodeMarker++;
    //breadth first search
    var queue = [];
    queue.push(node);
    //while Q is not empty
    var pos = 0;
    while(queue.length > 0 && pos < 500) {
        var node = queue.shift();
        
        //check the condition (which is the point of the search)
        if(conditionFunc(node)) {
            return {node: node, pos: pos};
        }
        
        var adjacentNodes = [];
        var parentNode = node.parentNode;
        if(parentNode) {
            adjacentNodes.push(parentNode);
            var siblings = getSiblings(parentNode);
            for(var k=0; k<siblings.length; k++) {
                var sib = siblings[k];
                if(!sib['visited' + nodeMarker]) {
                    sib['visited' + nodeMarker] = true;
                    adjacentNodes.push(sib);
                }
            }
        }
        //for all edges from v to w in G.adjacentEdges(v) do
        for(var i=0; i<adjacentNodes.length; i++) {
            var adjacentNode = adjacentNodes[i];
            queue.push(adjacentNode);
        }
        pos++;
    }
}

/**
 * Adds 'data-title' and 'data-url' attributes to 'div' parameter of postObj
 */
function createToolboxDiv(postObj) {
    var postDiv = document.createElement("div");
    if(postObj.title) {
        postDiv.setAttribute('data-title', postObj.title);
    }
    if(postObj.url) {
        postDiv.setAttribute('data-url', postObj.url);
    }
    return postDiv;
}

/**
 * Retrieves all of the nodes in window.document whose 'data'
 *   attributes contain 3 consecutive ASCII 0x43,0x45,or 0x42
 *
 * @return An array of DOM nodes
 */
function getChildNodesWithCondition(node, conditionFunc) {
    var descs = [];
    node = node || document;
    if(node) {
        var childNodes = node.childNodes;
        for(var i=0; i<childNodes.length; i++) {
            var childNode = childNodes[i];
            if(conditionFunc(childNode)) {
                descs.push(childNode);
            }
            descs = descs.concat(getChildNodesWithCondition(childNode, conditionFunc));
        }
    }
    return descs;
};

/**
 * Queries window.document for a 3-letter non-printing code
 *   The order of the code identifies a type of excerpt (archive, category, etc).
 *   Inserts sharetoolbox and recommendedbox divs on either side of the excerpt.
 *
 * @alters window.document
 */
function addDivsToCodedExcerpts() {
    for(var i=0; i<finalPostObjList.length; i++) {
        var postObj = finalPostObjList[i];
        var excerptNode = postObj.excerptNode;
        var excerptCode = excerptNode.data.substring(0,3);
        var suffix = "";

        if(excerptCode === String.fromCharCode(43,45,42)) {
            suffix = "-homepage";
        } else if(excerptCode === String.fromCharCode(43,42,45)) {
            suffix = "-page";
        } else if(excerptCode === String.fromCharCode(45,42,43)) {
            suffix = "";
        } else if(excerptCode === String.fromCharCode(45,43,42)) {
            suffix = "-cat-page";
        } else if(excerptCode === String.fromCharCode(42,43,45)) {
            suffix = "-arch-page";
        }

        var parentElement = excerptNode.parentElement;

        var above = createToolboxDiv(postObj);
        above.className = "at-above-post" + suffix;

        var below = createToolboxDiv(postObj);
        below.className = "at-below-post" + suffix;

        var aboveRecommended = createToolboxDiv(postObj);
        aboveRecommended.className = "at-above-post" + suffix + "-recommended";

        var belowRecommended = createToolboxDiv(postObj);
        belowRecommended.className = "at-below-post" + suffix + "-recommended";

        parentElement.appendChild(below);
        parentElement.appendChild(belowRecommended);

        parentElement.insertBefore(
            above, parentElement.childNodes[0]);
        parentElement.insertBefore(
            aboveRecommended, parentElement.childNodes[0]);

        excerptNode.data = excerptNode.data.replace(/[\+\-\*]{3}/g, "")
    }
}

function getChildren(n, skipMe){
    var r = [];
    for ( ; n; n = n.nextSibling ) 
       if ( n.nodeType == 1 && n != skipMe)
          r.push( n );        
    return r;
};

function getSiblings(n) {
    var siblings = [];
    if(n.parentNode) {
        siblings = getChildren(n.parentNode.firstChild, n);
    }
    return siblings;
}

/**
 * Returns the longest common substring
 */
function longestCommonSubstring(string1, string2){
	// init max value
	var longestCommonSubstring = 0;
	// init 2D array with 0
	var table = [],
            len1 = string1.length,
            len2 = string2.length,
            row, col;
	for(row = 0; row <= len1; row++){
		table[row] = [];
		for(col = 0; col <= len2; col++){
			table[row][col] = 0;
		}
	}
	// fill table
        var i, j;
	for(i = 0; i < len1; i++){
		for(j = 0; j < len2; j++){
			if(string1[i]==string2[j]){
				if(table[i][j] == 0){
					table[i+1][j+1] = 1;
				} else {
					table[i+1][j+1] = table[i][j] + 1;
				}
				if(table[i+1][j+1] > longestCommonSubstring){
					longestCommonSubstring = table[i+1][j+1];
				}
			} else {
				table[i+1][j+1] = 0;
			}
		}
	}
	return longestCommonSubstring;
}

var finalPostObjList = [];
associatePostObjWithExcerptNode(postTitlesAndUrls);
addDivsToCodedExcerpts(postTitlesAndUrls);