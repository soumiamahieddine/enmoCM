var BootstrapTree = {
    init: function(tree) {
        console.log('INIT');
        tree.find('li')
            .children('ul')
            .parent()
            .addClass('parent_li')
            .find('> span')
            .find('i:first')
            .on('click', BootstrapTree.toggleNode);

        $j('.parent_li').find('span:first').find('.fa:first').addClass('fa-plus-square');
        $j('.parent_li').find(' > ul > li').hide();
    },

    addRoot: function(tree, element) {
        if (!element || !tree) {
            return;
        }

        var ul = $j('<ul/>')

        element.appendTo(ul);
        ul.appendTo(tree);
    },

    addNode: function(parent, element) {
        console.log('ADD NODE');
        if (!element || !parent) {
            return;
        }

        var ul = parent.find('> ul');
        if (ul.length == 0) {
            ul = $j('<ul/>').appendTo(parent);

            parent.addClass('parent_li')
              .find('span:first')
              .find('.fa:first')
              .addClass('fa-minus-square')
              .on('click', BootstrapTree.toggleNode);
        }
        //this.openNode(ul);
        element.appendTo(ul);
    },

    removeNode: function(element) {
        console.log('REMOVE NODE');
        if (element.prop("tagName") != 'LI') {
            //console.log("log");
            return;
        }

        var ul = element.closest('ul');
        var li = ul.closest('li');

        if (ul.find('>li').length <= 1) {
            ul.remove();
            li.find('i.fa').removeClass('fa-minus-square fa-plus-square');
        } else {
            element.remove();
        }

        this.openNode(li);
    },
    //enleve les
    removeSons: function(ulElement){
        ulElement.children().remove();
    },

    openNode: function(element) {
        //console.log("OPEN ELEMENT:"+element);
        element.parents('li').find('i.fa-plus-square:first').click();
    },

    toggleNode: function(event) {
        var children = $j(this).closest('li.parent_li').find(' > ul > li');
        //console.log(children);
        if (children.is(':visible')) {
            console.log("VISIBLE");
            children.hide('fast');
            $j(this).parent().find(' > i').addClass('fa-plus-square').removeClass('fa-minus-square');
            //console.log('THIS.PARENT:'+$j(this).parent());
        }
        else {
            console.log("INVISIBLE");
            children.show('fast');
            $j(this).parent().find(' > i').addClass('fa-minus-square').removeClass('fa-plus-square');
            //console.log('THIS.PARENT:'+$j(this).parent());
        }
        event.stopPropagation();
        $j('.tree').find('.hideTreeElement').css('display', 'none');
    },

    findNode: function(tree, text) {
        //tree.find('i.fa-minus-square').click();
        this.openNode(tree.find("li:contains('"+text+"')"));
    }
}